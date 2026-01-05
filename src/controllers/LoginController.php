<?php

/**
 * Responsável por renderizar views de login e processar autenticação
 * 
 * @author MailJZTech
 * @date 2025-01-01
 */

namespace src\controllers;

use \core\Controller as ctrl;
use \src\handlers\UsuariosHandler;
use \src\handlers\service\TwoFactorAuthService;
use Exception;

class LoginController extends ctrl
{
    /**
     * Renderiza a página de login
     * GET /
     */
    public function index()
    {
        // Se já está logado, redireciona para dashboard
        if (UsuariosHandler::checkLogin()) {
            $this->redirect('dashboard');
        }

        $this->render('login');
    }

    /**
     * Processa o login do usuário - ROTA UNIFICADA
     * POST /login
     * 
     * Cenários suportados:
     * 1. Login inicial (email + senha)
     * 2. Login com 2FA (email + senha + codigo_2fa)
     * 3. Iniciar config 2FA (email + senha + acao: "iniciar_2fa")
     * 4. Confirmar config 2FA (email + senha + acao: "confirmar_2fa" + codigo)
     */
    public function verificarLogin()
    {
        try {
            $dados = ctrl::getBody();
            $email = $dados['email'] ?? null;
            $senha = $dados['senha'] ?? null;
            $acao = $dados['acao'] ?? null;

            // Validação básica
            if (empty($email) || empty($senha)) {
                throw new Exception('Email e senha são obrigatórios');
            }

            // Verifica credenciais
            $usuario = UsuariosHandler::verificarCredenciais($email, $senha);
            if (isset($usuario['error'])) {
                throw new Exception($usuario['error']);
            }

            // CENÁRIO 1: Iniciar configuração 2FA (primeira vez)
            if ($acao === 'iniciar_2fa') {
                ctrl::response($this->iniciarDoisFatores($usuario), 200);
                return;
            }

            // CENÁRIO 2: Confirmar configuração 2FA
            if ($acao === 'confirmar_2fa') {
                $codigo = $dados['codigo'] ?? null;
                ctrl::response($this->confirmarDoisFatores($usuario, $codigo), 200);
                return;
            }

            // CENÁRIO 3: Verificar código 2FA durante login
            if (!empty($dados['codigo_2fa'])) {
                ctrl::response($this->verificarCodigoTotp($usuario, $dados['codigo_2fa']), 200);
                return;
            }

            // CENÁRIO 4: Login inicial (apenas email + senha)
            // Verifica se usuário tem 2FA habilitado
            $usuarioTem2FA = $this->usuarioTemDoisFatoresAtivo($usuario);
            
            if ($usuarioTem2FA) {
                // Tem 2FA: pedir código
                ctrl::response([
                    'success' => true,
                    'requer_2fa' => true,
                    'idusuario' => $usuario['idusuario'],
                    'mensagem' => 'Insira o código do autenticador'
                ], 200);
            } else {
                // Não tem 2FA: precisa configurar ou login direto
                // Por segurança, exigimos 2FA - retorna para configurar
                ctrl::response([
                    'success' => true,
                    'requer_2fa' => false,
                    'configurar_2fa' => true,
                    'idusuario' => $usuario['idusuario'],
                    'mensagem' => 'Configure 2FA para continuar'
                ], 200);
            }
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * [INTERNO] Inicia configuração de 2FA gerando secret e QR Code
     * @param array $usuario Dados do usuário
     * @return array Dados para configuração (QR, secret)
     */
    private function iniciarDoisFatores($usuario)
    {
        $idusuario = $usuario['idusuario'];
        $idempresa = $usuario['idempresa'];
        $email = $usuario['email'];

        if ($this->usuarioTemDoisFatoresAtivo($usuario)) {
            throw new Exception('2FA já configurado para este usuário');
        }
        
        // Gera secret e dados para QR Code
        $dados2fa = UsuariosHandler::habilitar2FA($idempresa, $idusuario);

        return [
            'success' => true,
            'idusuario' => $idusuario,
            'secret' => $dados2fa['secret'],
            'qr_code_url' => $dados2fa['qrcode_url'],
            'uri' => $dados2fa['uri']
        ];
    }

    /**
     * [INTERNO] Processa a confirmação de 2FA
     * @param array $usuario Dados do usuário
     * @param string $codigo Código de 6 dígitos
     * @return array Resposta de sucesso
     */
    private function confirmarDoisFatores($usuario, $codigo)
    {
        $idusuario = $usuario['idusuario'];
        $idempresa = $usuario['idempresa'];

        if (empty($codigo)) {
            throw new Exception('Código é obrigatório');
        }

        // Confirma 2FA
        $resultado = UsuariosHandler::confirmar2FA($idempresa, $idusuario, $codigo);

        if (isset($resultado['success']) && $resultado['success']) {
            // Realiza login após confirmar 2FA
            $usuarioLogado = UsuariosHandler::realizarLogin($usuario);

            return [
                'success' => true,
                'mensagem' => '2FA configurado com sucesso',
                'token' => $usuarioLogado['token']
            ];
        }

        throw new Exception('Falha ao confirmar 2FA');
    }

    /**
     * Realiza o logout do usuário
     * GET /sair (privado = true)
     */
    public function logout()
    {
        try {
            UsuariosHandler::logout();
            ctrl::redirect('/login');
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }

    /**
     * [INTERNO] Verifica o código TOTP durante login
     * @param array $usuario Dados do usuário
     * @param string $codigo Código de 6 dígitos
     * @return array Resposta de sucesso
     */
    private function verificarCodigoTotp($usuario, $codigo)
    {
        if (empty($codigo)) {
            throw new Exception('Código é obrigatório');
        }

        if (empty($usuario['totp_secret'])) {
            throw new Exception('Usuário não possui 2FA configurado');
        }

        // Verifica o código TOTP
        if (!TwoFactorAuthService::verificarCodigo($usuario['totp_secret'], $codigo)) {
            throw new Exception('Código TOTP inválido');
        }

        // Realiza login
        $usuarioLogado = UsuariosHandler::realizarLogin($usuario);

        return [
            'success' => true,
            'mensagem' => '2FA verificado com sucesso',
            'token' => $usuarioLogado['token']
        ];
    }

    /**
     * Normaliza o estado do 2FA
     */
    private function usuarioTemDoisFatoresAtivo(array $usuario): bool
    {
        $possuiSecret = !empty($usuario['totp_secret']);
        $flagHabilitado = $usuario['totp_habilitado'] ?? 0;

        return $possuiSecret && $flagHabilitado == 1;
    }

    /**
     * Valida o token do usuário
     * GET /validaToken
     */
    public function validaToken()
    {
        try {
            $headers = getallheaders();
            $tk = isset($headers['Authorization']) ? $headers['Authorization'] : null;
            $tk2 = isset($_REQUEST['jwt']) ? 'Bearer ' . $_REQUEST['jwt'] : null;
            $token = (!empty($tk) && strlen($tk) > 8) ? $tk : $tk2;

            if (isset($_SESSION['token']) && !empty($_SESSION['token']) && $token == 'Bearer ' . $_SESSION['token']) {
                $infos = UsuariosHandler::checkLogin() ? ['token' => $_SESSION['token']] : null;
                if (!empty($infos)) {
                    ctrl::response($infos, 200);
                    return;
                }
            }
            throw new Exception('Token inválido');
        } catch (Exception $e) {
            ctrl::rejectResponse($e);
        }
    }
}
