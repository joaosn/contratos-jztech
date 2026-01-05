<?php

namespace src\handlers;

use src\models\ClientesModel;
use src\models\ClientesEnderecosModel;
use src\models\ClientesContatosModel;
use core\Database;
use Exception;

/**
 * Handler para lógica de negócio de clientes
 * Responsabilidade: Validações e orquestração de models
 */
class ClientesHandler {
    
    private $clientesModel;
    private $enderecosModel;
    private $contatosModel;

    public function __construct() {
        $this->clientesModel = new ClientesModel();
        $this->enderecosModel = new ClientesEnderecosModel();
        $this->contatosModel = new ClientesContatosModel();
    }

    /**
     * Lista clientes com filtros e paginação
     */
    public function listarClientes($filtros = []) {
        // Validações de filtros
        if (isset($filtros['tipo_pessoa']) && !in_array($filtros['tipo_pessoa'], ['pf', 'pj'])) {
            throw new Exception("Tipo de pessoa inválido. Use 'pf' ou 'pj'");
        }

        if (isset($filtros['ativo']) && !in_array($filtros['ativo'], [0, 1])) {
            throw new Exception("Status ativo inválido. Use 0 ou 1");
        }

        // Paginação padrão
        $filtros['limit'] = min($filtros['limit'] ?? 50, 100); // Máximo 100 registros
        $filtros['offset'] = max($filtros['offset'] ?? 0, 0);

        $clientes = $this->clientesModel->listarTodos($filtros);
        $total = $this->clientesModel->contarTodos($filtros);

        return [
            'success' => true,
            'data' => $clientes,
            'total' => $total,
            'pagination' => [
                'limit' => $filtros['limit'],
                'offset' => $filtros['offset'],
                'has_more' => ($filtros['offset'] + $filtros['limit']) < $total
            ]
        ];
    }

    /**
     * Busca cliente por ID com endereços e contatos
     */
    public function buscarClienteCompleto($idcliente) {
        $cliente = $this->clientesModel->buscarPorId($idcliente);
        
        if (!$cliente) {
            throw new Exception("Cliente não encontrado");
        }

        // Busca endereços e contatos
        $cliente['enderecos'] = $this->enderecosModel->listarPorCliente($idcliente);
        $cliente['contatos'] = $this->contatosModel->listarPorCliente($idcliente);

        return [
            'success' => true,
            'data' => $cliente
        ];
    }

    /**
     * Busca cliente por CPF/CNPJ
     */
    public function buscarPorCpfCnpj($cpf_cnpj) {
        // Validação básica de CPF/CNPJ
        $cpf_cnpj = $this->limparCpfCnpj($cpf_cnpj);
        
        if (!$this->validarCpfCnpj($cpf_cnpj)) {
            throw new Exception("CPF/CNPJ inválido");
        }

        $cliente = $this->clientesModel->buscarPorCpfCnpj($cpf_cnpj);

        if (!$cliente) {
            return [
                'success' => false,
                'message' => 'Cliente não encontrado'
            ];
        }

        return [
            'success' => true,
            'data' => $cliente
        ];
    }

    /**
     * Pesquisa clientes por termo
     */
    public function pesquisarClientes($termo, $filtros = []) {
        if (strlen(trim($termo)) < 2) {
            throw new Exception("Termo de pesquisa deve ter pelo menos 2 caracteres");
        }

        $filtros['limit'] = min($filtros['limit'] ?? 50, 100);
        $filtros['offset'] = max($filtros['offset'] ?? 0, 0);

        $clientes = $this->clientesModel->pesquisar($termo, $filtros);

        return [
            'success' => true,
            'data' => $clientes,
            'termo' => $termo
        ];
    }

    /**
     * Cria novo cliente
     */
    public function criarCliente($dados) {
        // Validações obrigatórias
        $this->validarDadosCliente($dados);

        // Limpa e valida CPF/CNPJ
        $dados['cpf_cnpj'] = $this->limparCpfCnpj($dados['cpf_cnpj']);
        
        if (!$this->validarCpfCnpj($dados['cpf_cnpj'])) {
            throw new Exception("CPF/CNPJ inválido");
        }

        // Verifica se CPF/CNPJ já existe
        $clienteExistente = $this->clientesModel->buscarPorCpfCnpj($dados['cpf_cnpj']);
        if ($clienteExistente) {
            throw new Exception("CPF/CNPJ já cadastrado para outro cliente");
        }

        // Validações específicas por tipo
        if ($dados['tipo_pessoa'] === 'pj' && empty($dados['nome_fantasia'])) {
            $dados['nome_fantasia'] = $dados['nome']; // Usa razão social como padrão
        }

        $idcliente = $this->clientesModel->inserir($dados);

        return [
            'success' => true,
            'message' => 'Cliente criado com sucesso',
            'idcliente' => $idcliente
        ];
    }

    /**
     * Atualiza cliente existente
     */
    public function atualizarCliente($idcliente, $dados) {
        // Verifica se cliente existe
        $clienteExistente = $this->clientesModel->buscarPorId($idcliente);
        if (!$clienteExistente) {
            throw new Exception("Cliente não encontrado");
        }

        // Validações
        $this->validarDadosCliente($dados);

        // Valida CPF/CNPJ se foi alterado
        $dados['cpf_cnpj'] = $this->limparCpfCnpj($dados['cpf_cnpj']);
        
        if ($dados['cpf_cnpj'] !== $clienteExistente['cpf_cnpj']) {
            if (!$this->validarCpfCnpj($dados['cpf_cnpj'])) {
                throw new Exception("CPF/CNPJ inválido");
            }

            // Verifica se novo CPF/CNPJ já existe
            $outroCliente = $this->clientesModel->buscarPorCpfCnpj($dados['cpf_cnpj']);
            if ($outroCliente && $outroCliente['idcliente'] != $idcliente) {
                throw new Exception("CPF/CNPJ já cadastrado para outro cliente");
            }
        }

        $this->clientesModel->atualizar($idcliente, $dados);

        return [
            'success' => true,
            'message' => 'Cliente atualizado com sucesso'
        ];
    }

    /**
     * Exclui cliente (apenas se não tiver assinaturas ativas)
     */
    public function excluirCliente($idcliente) {
        $cliente = $this->clientesModel->buscarPorId($idcliente);
        if (!$cliente) {
            throw new Exception("Cliente não encontrado");
        }

        // Verifica se pode excluir
        if (!$this->clientesModel->podeExcluir($idcliente)) {
            throw new Exception("Não é possível excluir cliente com assinaturas ativas");
        }

        $this->clientesModel->excluir($idcliente);

        return [
            'success' => true,
            'message' => 'Cliente excluído com sucesso'
        ];
    }

    /**
     * Gerencia endereços do cliente
     */
    public function adicionarEndereco($dados) {
        // Validações
        $this->validarDadosEndereco($dados);

        // Verifica se cliente existe
        $cliente = $this->clientesModel->buscarPorId($dados['idcliente']);
        if (!$cliente) {
            throw new Exception("Cliente não encontrado");
        }

        // Se é principal, remove principal dos outros
        if ($dados['principal'] == 1) {
            $this->enderecosModel->removerTodosPrincipais($dados['idcliente']);
        }

        $idendereco = $this->enderecosModel->inserir($dados);

        return [
            'success' => true,
            'message' => 'Endereço adicionado com sucesso',
            'idendereco' => $idendereco
        ];
    }

    /**
     * Define endereço como principal
     */
    public function definirEnderecoPrincipal($idendereco) {
        $endereco = $this->enderecosModel->buscarPorId($idendereco);
        if (!$endereco) {
            throw new Exception("Endereço não encontrado");
        }

        // Remove principal dos outros endereços do cliente
        $this->enderecosModel->removerTodosPrincipais($endereco['idcliente']);
        
        // Define este como principal
        $this->enderecosModel->definirPrincipal($idendereco);

        return [
            'success' => true,
            'message' => 'Endereço definido como principal'
        ];
    }

    /**
     * Gerencia contatos do cliente
     */
    public function adicionarContato($dados) {
        // Validações
        $this->validarDadosContato($dados);

        // Verifica se cliente existe
        $cliente = $this->clientesModel->buscarPorId($dados['idcliente']);
        if (!$cliente) {
            throw new Exception("Cliente não encontrado");
        }

        // Se é principal, remove principal dos outros
        if ($dados['principal'] == 1) {
            $this->contatosModel->removerTodosPrincipais($dados['idcliente']);
        }

        $idcontato = $this->contatosModel->inserir($dados);

        return [
            'success' => true,
            'message' => 'Contato adicionado com sucesso',
            'idcontato' => $idcontato
        ];
    }

    /**
     * Define contato como principal
     */
    public function definirContatoPrincipal($idcontato) {
        $contato = $this->contatosModel->buscarPorId($idcontato);
        if (!$contato) {
            throw new Exception("Contato não encontrado");
        }

        // Remove principal dos outros contatos do cliente
        $this->contatosModel->removerTodosPrincipais($contato['idcliente']);
        
        // Define este como principal
        $this->contatosModel->definirPrincipal($idcontato);

        return [
            'success' => true,
            'message' => 'Contato definido como principal'
        ];
    }

    /**
     * Lista endereços do cliente
     */
    public function listarEnderecos($idcliente) {
        // Verifica se cliente existe
        $cliente = $this->clientesModel->buscarPorId($idcliente);
        if (!$cliente) {
            throw new Exception("Cliente não encontrado");
        }

        $enderecos = $this->enderecosModel->listarPorCliente($idcliente);

        return [
            'success' => true,
            'data' => $enderecos
        ];
    }

    /**
     * Lista contatos do cliente
     */
    public function listarContatos($idcliente) {
        // Verifica se cliente existe
        $cliente = $this->clientesModel->buscarPorId($idcliente);
        if (!$cliente) {
            throw new Exception("Cliente não encontrado");
        }

        $contatos = $this->contatosModel->listarPorCliente($idcliente);

        return [
            'success' => true,
            'data' => $contatos
        ];
    }

    /**
     * Validações privadas
     */
    private function validarDadosCliente($dados) {
        if (empty($dados['tipo_pessoa']) || !in_array($dados['tipo_pessoa'], ['pf', 'pj'])) {
            throw new Exception("Tipo de pessoa é obrigatório (pf/pj)");
        }

        if (empty($dados['nome'])) {
            throw new Exception("Nome é obrigatório");
        }

        if (empty($dados['cpf_cnpj'])) {
            throw new Exception("CPF/CNPJ é obrigatório");
        }

        if (isset($dados['email']) && !empty($dados['email']) && !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email inválido");
        }
    }

    private function validarDadosEndereco($dados) {
        if (empty($dados['idcliente'])) {
            throw new Exception("ID do cliente é obrigatório");
        }

        if (empty($dados['tipo'])) {
            throw new Exception("Tipo do endereço é obrigatório");
        }

        if (empty($dados['logradouro'])) {
            throw new Exception("Logradouro é obrigatório");
        }

        if (empty($dados['cidade'])) {
            throw new Exception("Cidade é obrigatória");
        }

        if (empty($dados['uf']) || strlen($dados['uf']) !== 2) {
            throw new Exception("UF é obrigatória e deve ter 2 caracteres");
        }
    }

    private function validarDadosContato($dados) {
        if (empty($dados['idcliente'])) {
            throw new Exception("ID do cliente é obrigatório");
        }

        if (empty($dados['nome'])) {
            throw new Exception("Nome do contato é obrigatório");
        }

        if (isset($dados['email']) && !empty($dados['email']) && !filter_var($dados['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Email inválido");
        }
    }

    private function limparCpfCnpj($cpf_cnpj) {
        return preg_replace('/[^0-9]/', '', $cpf_cnpj);
    }

    private function validarCpfCnpj($cpf_cnpj) {
        $cpf_cnpj = $this->limparCpfCnpj($cpf_cnpj);
        
        if (strlen($cpf_cnpj) == 11) {
            return $this->validarCpf($cpf_cnpj);
        } elseif (strlen($cpf_cnpj) == 14) {
            return $this->validarCnpj($cpf_cnpj);
        }
        
        return false;
    }

    private function validarCpf($cpf) {
        // Validação básica de CPF
        if (strlen($cpf) != 11 || preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Cálculo dos dígitos verificadores
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }

        return true;
    }

    private function validarCnpj($cnpj) {
        // Validação básica de CNPJ
        if (strlen($cnpj) != 14) {
            return false;
        }

        // Cálculo dos dígitos verificadores
        $b = [6, 7, 8, 9, 2, 3, 4, 5, 6, 7, 8, 9];

        for ($i = 0, $n = 0; $i < 12; $n += $cnpj[$i] * $b[++$i]);
        if ($cnpj[12] != ((($n %= 11) < 2) ? 0 : 11 - $n)) {
            return false;
        }

        for ($i = 0, $n = 0; $i <= 12; $n += $cnpj[$i] * $b[$i++]);
        if ($cnpj[13] != ((($n %= 11) < 2) ? 0 : 11 - $n)) {
            return false;
        }

        return true;
    }
}