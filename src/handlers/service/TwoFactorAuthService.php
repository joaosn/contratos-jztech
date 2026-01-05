<?php

/**
 * Serviço de autenticação de dois fatores (2FA) usando TOTP
 * Utiliza biblioteca spomky-labs/otphp
 */

namespace src\handlers\service;

use OTPHP\TOTP;

class TwoFactorAuthService
{
    /**
     * Nome do emissor exibido no app autenticador
     */
    const ISSUER = 'OrganizaAI';

    /**
     * Período de validade do código em segundos (padrão: 30)
     */
    const PERIOD = 30;

    /**
     * Número de dígitos do código (padrão: 6)
     */
    const DIGITS = 6;

    /**
     * Gera um novo secret TOTP e retorna dados para configuração
     * 
     * @param string $email Email do usuário (usado como label)
     * @return array ['secret' => string, 'uri' => string, 'qrcode_url' => string]
     */
    public static function gerarSecret($email)
    {
        // Cria TOTP com secret aleatório
        $totp = TOTP::create();
        
        // Configura parâmetros
        $totp->setLabel($email);
        $totp->setIssuer(self::ISSUER);
        $totp->setPeriod(self::PERIOD);
        $totp->setDigits(self::DIGITS);

        $secret = $totp->getSecret();
        $uri = $totp->getProvisioningUri();

        // URL para gerar QR Code via Google Charts API
        $qrcodeUrl = 'https://chart.googleapis.com/chart?chs=200x200&cht=qr&chl=' . urlencode($uri);

        return [
            'secret' => $secret,
            'uri' => $uri,
            'qrcode_url' => $qrcodeUrl
        ];
    }

    /**
     * Verifica se o código TOTP fornecido é válido
     * 
     * @param string $secret Secret do usuário
     * @param string $codigo Código de 6 dígitos fornecido
     * @param int $janela Janela de tolerância (padrão: 1 = aceita código anterior/posterior)
     * @return bool
     */
    public static function verificarCodigo($secret, $codigo, $janela = 1)
    {
        if (empty($secret) || empty($codigo)) {
            return false;
        }

        try {
            $totp = TOTP::create($secret);
            $totp->setPeriod(self::PERIOD);
            $totp->setDigits(self::DIGITS);

            // Verifica com janela de tolerância
            return $totp->verify($codigo, null, $janela);
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Gera o código atual para um secret (útil para testes)
     * 
     * @param string $secret Secret do usuário
     * @return string Código de 6 dígitos
     */
    public static function gerarCodigoAtual($secret)
    {
        $totp = TOTP::create($secret);
        $totp->setPeriod(self::PERIOD);
        $totp->setDigits(self::DIGITS);

        return $totp->now();
    }

    /**
     * Retorna quantos segundos restam para o código atual expirar
     * 
     * @return int Segundos restantes
     */
    public static function segundosRestantes()
    {
        return self::PERIOD - (time() % self::PERIOD);
    }

    /**
     * Valida formato do código (6 dígitos numéricos)
     * 
     * @param string $codigo
     * @return bool
     */
    public static function validarFormato($codigo)
    {
        return preg_match('/^\d{6}$/', $codigo) === 1;
    }
}
