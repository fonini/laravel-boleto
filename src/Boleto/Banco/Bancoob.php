<?php
namespace Eduardokum\LaravelBoleto\Boleto\Banco;

use Eduardokum\LaravelBoleto\Boleto\AbstractBoleto;
use Eduardokum\LaravelBoleto\CalculoDV;
use Eduardokum\LaravelBoleto\Contracts\Boleto\Boleto as BoletoContract;
use Eduardokum\LaravelBoleto\Util;

class Bancoob extends AbstractBoleto implements BoletoContract
{
    public function __construct(array $params = [])
    {
        parent::__construct($params);
        $this->addCampoObrigatorio('convenio');
    }

    /**
     * Código do banco
     * @var string
     */
    protected $codigoBanco = self::COD_BANCO_BANCOOB;
    /**
     * Define as carteiras disponíveis para este banco
     * @var array
     */
    protected $carteiras = ['1','3'];
    /**
     * Espécie do documento, código para remessa do CNAB240
     * @var string
     */
    protected $especiesCodigo = [
        //Equivalentes ao CNAB240
        'CH'  => '01', //Cheque
        'DM'  => '02', //Duplicata Mercantil
        'DMI' => '03', //Duplicata Mercantil p/ Indicação
        'DS'  => '04', //Duplicata de Serviço
        'DSI' => '05', //Duplicata de Serviço p/ Indicação
        'DR'  => '06', //Duplicata Rural
        'LC'  => '07', //Letra de Câmbio
        'NCC' => '08', //Nota de Crédito Comercial
        'NCE' => '09', //Nota de Crédito a Exportação
        'NCI' => '10', //Nota de Crédito Industrial
        'NCR' => '11', //Nota de Crédito Rural
        'NP'  => '12', //Nota Promissória
        'NPR' => '13', //Nota Promissória Rural
        'TM'  => '14', //Triplicata Mercantil
        'TS'  => '15', //Triplicata de Serviço
        'NS'  => '16', //Nota de Seguro
        'RC'  => '17', //Recibo
        'FAT' => '18', //Fatura
        'ND'  => '19', //Nota de Débito
        'AP'  => '20', //Apólice de Seguro
        'ME'  => '21', //Mensalidade Escolar
        'PC'  => '22', //Parcela de Consórcio
        'NF'  => '23', //Nota Fiscal
        'DD'  => '24', //Documento de Dívida
        'CPR' => '25',  //Cédula de Produto Rural,
        'O'   => '99',  //Outros,
        //Equivalente no CNAB400 que não existe no CNAB240
        'W'   => '100',  //Warrant CNAB400
    ];

     /**
     * Código do cliente (é código do cedente, também chamado de código do beneficiário) é o código do emissor junto ao banco, geralmente é o próprio número da conta sem o dígito verificador. 
     * O código do cliente/cedente/beneficiário será diferente desse padrão em casos como quando um cliente bancário faz a migração da sua conta entre agências.
     *
     * @var string
     */
    protected $codigoCliente;

    /**
     * Define o número do convênio (4, 6 ou 7 caracteres)
     *
     * @var string
     */
    protected $convenio;
    /**
     * Define o número do convênio. Sempre use string pois a quantidade de caracteres é validada.
     *
     * @param  string $convenio
     * @return Bancoob
     */
    public function setConvenio($convenio)
    {
        $this->convenio = $convenio;
        return $this;
    }
    /**
     * Retorna o número do convênio
     *
     * @return string
     */
    public function getConvenio()
    {
        return $this->convenio;
    }
    /**
     * Gera o Nosso Número.
     *
     * @throws \Exception
     * @return string
     */
    protected function gerarNossoNumero()
    {
        return Util::numberFormatGeral($this->getNumero(), 7)
            . CalculoDV::bancoobNossoNumero($this->getAgencia(), $this->getConvenio(), $this->getNumero());
    }
    /**
     * Método que retorna o nosso numero usado no boleto. alguns bancos possuem algumas diferenças.
     *
     * @return string
     */
    public function getNossoNumeroBoleto()
    {
        return $this->getNossoNumero();
    }

     /**
     * Retorna o Nosso Número.
     *
     * @return string
     */
    public function getNossoNumero()
    {
        return $this->numero;
    }

    /**
     * Seta o codigo do cliente.
     *
     * @param mixed $codigoCliente
     *
     * @return $this
     */
    public function setCodigoCliente($codigoCliente)
    {
        $this->codigoCliente = $codigoCliente;

        return $this;
    }
    /**
     * Retorna o codigo do cliente.
     *
     * @return string
     */
    public function getCodigoCliente()
    {
        return $this->codigoCliente;
    }

    /**
     * Método para gerar o código da posição de 20 a 44
     *
     * @return string
     * @throws \Exception
     */
    protected function getCampoLivre()
    {
        if ($this->campoLivre) {
            return $this->campoLivre;
        }

        $nossoNumero = $this->getNossoNumero();

        $campoLivre = Util::numberFormatGeral($this->getCarteira(), 1);
        $campoLivre .= Util::numberFormatGeral($this->getAgencia(), 4);
        $campoLivre .= Util::numberFormatGeral($this->getCarteira(), 2);
        $campoLivre .= Util::numberFormatGeral($this->getConvenio(), 7);
        $campoLivre .= Util::numberFormatGeral($nossoNumero, 8);
        $campoLivre .= Util::numberFormatGeral(1, 3); //Numero da parcela - Não implementado

        return $this->campoLivre = $campoLivre;
    }

    public function setCodigoBarras($codigoBarras)
    {
        $this->campoCodigoBarras = $codigoBarras;
        return $this;
    }

    public function setLinhaDigitavel($linhaDigitavel)
    {

        $str = substr($linhaDigitavel, 0, 5).'.'.substr($linhaDigitavel, 5, 5).' '.substr($linhaDigitavel, 10, 5);
        $str .= '.'.substr($linhaDigitavel, 15, 6).' '.substr($linhaDigitavel, 21, 5).'.'.substr($linhaDigitavel, 26, 6);
        $str .= ' '.substr($linhaDigitavel, 32, 1).' '.substr($linhaDigitavel, 33);

        $this->campoLinhaDigitavel = $str;
        return $this;
    }

    /**
     * Método onde qualquer boleto deve extender para gerar o código da posição de 20 a 44
     *
     * @param $campoLivre
     *
     * @return array
     */
    static public function parseCampoLivre($campoLivre) {
        return [
            'codigoCliente' => null,
            'agenciaDv' => null,
            'contaCorrente' => null,
            'contaCorrenteDv' => null,
            'carteira' => substr($campoLivre, 0, 1),
            'agencia' => substr($campoLivre, 1, 4),
            'modalidade' => substr($campoLivre, 5, 2),
            'convenio' => substr($campoLivre, 7, 7),
            'nossoNumero' => substr($campoLivre, 14, 7),
            'nossoNumeroDv' => substr($campoLivre, 21, 1),
            'nossoNumeroFull' => substr($campoLivre, 14, 8),
            'parcela' => substr($campoLivre, 22, 3),
        ];
    }


    /**
     * Agência/Código do Beneficiário: Informar o prefixo da agência e o código de associado/cliente.
     * Estes dados constam na planilha "Capa" deste arquivo. O código de cliente não deve ser
     * confundido com o número da conta corrente, pois são códigos diferentes.
     * @return string
     */
    public function getAgenciaCodigoBeneficiario(){
        return $this->getAgencia() . ' / ' . $this->getCodigoCliente();
    }
    
}
