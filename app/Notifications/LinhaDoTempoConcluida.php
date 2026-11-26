<?php

declare(strict_types=1);

namespace App\Notifications;

use App\Enums\StatusSimulacao;
use App\Models\LinhaDoTempo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Aviso de fim da linha do tempo (todos os anos simulados): no app e por e-mail.
 */
class LinhaDoTempoConcluida extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly LinhaDoTempo $linhaDoTempo,
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
        return (new MailMessage)
            ->subject("Linha do tempo da simulação #{$this->linhaDoTempo->simulacao_id} concluída — Reforma360")
            ->greeting('Olá!')
            ->line($this->resumo())
            ->action('Ver linha do tempo', route('simulacoes.linha-do-tempo.show', $this->linhaDoTempo->simulacao_id))
            ->line('Os valores de 2027 em diante usam alíquotas plenas estimadas e são simulações; não substituem a orientação de um contador.')
            ->salutation('Equipe Reforma360');
    }

    /**
     * @return array{simulacao_id: int, linha_do_tempo_id: int, status: string, mensagem: string}
     */
    public function toArray(object $notifiable): array
    {
        return [
            'simulacao_id'      => $this->linhaDoTempo->simulacao_id,
            'linha_do_tempo_id' => $this->linhaDoTempo->id,
            'status'            => $this->linhaDoTempo->status->value,
            'mensagem'          => $this->resumo(),
        ];
    }

    private function resumo(): string
    {
        $id = $this->linhaDoTempo->simulacao_id;

        return match ($this->linhaDoTempo->status) {
            StatusSimulacao::Concluida         => "A linha do tempo 2026→2033 da simulação #{$id} está pronta.",
            StatusSimulacao::ConcluidaComErros => "A linha do tempo 2026→2033 da simulação #{$id} está pronta, com itens que não puderam ser simulados em alguns anos.",
            default                            => "A linha do tempo da simulação #{$id} não pôde ser concluída.",
        };
    }
}
