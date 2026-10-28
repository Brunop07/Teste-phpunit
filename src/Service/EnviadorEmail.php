<?php

namespace Alura\Leilao\Service;

use Alura\Leilao\Model\Leilao;

class EnviadorEmail
{
    public function notificarTerminoLeilao(Leilao $leilao): void
    {
        $sucesso = mail (
            'usuario@email.com',
            'leilao finalizado',
            'O leilao para'. $leilao->recuperarDescricao() . ' foi finalizado ');
        
        if (!$sucesso) {
            throw new \DomainException('Erro ao enviar email');
        }    
    }
}