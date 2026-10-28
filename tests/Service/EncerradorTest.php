<?php

namespace Alura\Leilao\Tests\Domain;

use Alura\Leilao\Dao\Leilao as LeilaoDao;
use Alura\Leilao\Model\Leilao;
use Alura\Leilao\Service\Encerrador;
use Alura\Leilao\Service\EnviadorEmail;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\MockObject\MockObject;

class EncerradorTest extends TestCase
    {

        private $encerrador;
        /** @var EnviadorEmail|MockObject */
        private $enviadorEmail;
        private $leilaoFiat;
        private $leilaoVariante;
        /** @var LeilaoDao|MockObject */
        private $leilaoDao;

        protected function setUp(): void
        {
            $this->leilaoFiat = new Leilao('Fiat 147 0Km', new \DateTimeImmutable('8 days ago'));
            $this->leilaoVariante = new Leilao('Variante 0Km', new \DateTimeImmutable('10 days ago'));

            $this->leilaoDao = $this->createMock(LeilaoDao::class);
            $this->leilaoDao->method('recuperarNaoFinalizados')
                ->willReturn([$this->leilaoFiat, $this->leilaoVariante]);
            $this->leilaoDao->expects($this->exactly(2))
                ->method('atualiza')
                ->withConsecutive(
                    [$this->leilaoFiat],
                    [$this->leilaoVariante]
                );
            $this->enviadorEmail = $this->createMock(EnviadorEmail::class);
            $this->encerrador = new Encerrador($this->leilaoDao, $this->enviadorEmail);
        }

        public function testLeiloesComMaisDeUmaSemanaDevemSerEncerrados()
        {
            $this->encerrador->encerra();
            $leiloes = [$this->leilaoFiat, $this->leilaoVariante];
            static::assertCount(2, $leiloes);
            static::assertTrue($leiloes[0]->estaFinalizado());
            static::assertTrue($leiloes[1]->estaFinalizado());
        }

        public function testDeveContinuarOProcessoamentoAoEncontrarErroAoEnviarEmail()
        {
            $e = new \DomainException('Erro ao enviar e-mail');
            $this->enviadorEmail->expects(self::exactly(2))
                ->method('notificarTerminoLeilao')
                ->willThrowException($e);

            $this->encerrador->encerra();
        }

        public function testSoDeveEnviarLeilaoPorEmailAposFinalizar()
        {
            $this->enviadorEmail->expects($this->exactly(2))
                ->method('notificarTerminoLeilao')
                ->withConsecutive(
                    [$this->leilaoFiat],
                    [$this->leilaoVariante]
                );

            $this->encerrador->encerra();
        }
    }