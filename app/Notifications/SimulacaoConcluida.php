<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\StatusSimulacao;
use App\Models\Simulacao;
use App\Support\Formatador;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Aviso de fim da simulação em lote: no app (banco) e por e-mail.
 */
class SimulacaoConcluida extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly Simulacao $simulacao,
    ) {}

    /**
     * @return list<string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $simulacao = $this->simulacao;

        $mensagem = (new MailMessage)
            ->subject("Simulação #{$simulacao->id} concluída — Reforma360")
            ->greeting('Olá!')
            ->line($this->resumo())
            ->action('Ver resultado', route('simulacoes.show', $simulacao));

        if ($simulacao->total_tributos !== null) {
            $mensagem->line('CBS + IBS estimados: '.Formatador::moeda($simulacao->total_tributos).'.');
        }

        return $mensagem
            ->line('Os valores são simulações e não substituem a orientação de um contador.')
            ->salutation('Equipe Reforma360');
    }

    /**
     * @return array{simulacao_id: int, status: string, mensagem: string}
     */
    public function toArray(object $notifiable): array
    {
        return [
            'simulacao_id' => $this->simulacao->id,
            'status'       => $this->simulacao->status->value,
            'mensagem'     => $this->resumo(),
        ];
    }

    private function resumo(): string
    {
        $simulacao = $this->simulacao;
        $calculados = $simulacao->itens_processados - $simulacao->itens_com_erro;

        return match ($simulacao->status) {
            StatusSimulacao::Concluida         => "A simulação #{$simulacao->id} terminou: {$calculados} itens simulados.",
            StatusSimulacao::ConcluidaComErros => "A simulação #{$simulacao->id} terminou: {$calculados} itens simulados e {$simulacao->itens_com_erro} com erro.",
            default                            => "A simulação #{$simulacao->id} não pôde ser concluída.",
        };
    }
}
