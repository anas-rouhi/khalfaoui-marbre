<?php

namespace App\Notifications;

use App\Models\Devis;
use App\Models\InstallationRate;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

/**
 * Alerte le gérant dès qu'une demande de devis arrive, pour qu'il n'ait pas à
 * surveiller le back-office.
 */
class NewDevisReceived extends Notification
{
    public function __construct(public Devis $devis) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $devis = $this->devis->loadMissing('product');

        // En français quelle que soit la langue du visiteur : l'alerte est
        // destinée au gérant.
        $application = InstallationRate::labelFor($devis->application, 'fr');

        $mail = (new MailMessage)
            ->subject("Nouvelle demande de devis — {$devis->client_name} ({$devis->reference})")
            ->greeting('Nouvelle demande de devis')
            ->line("**Client :** {$devis->client_name}")
            ->line("**Téléphone :** {$devis->phone}");

        if ($devis->email) {
            $mail->line("**E-mail :** {$devis->email}");
        }

        if ($devis->location) {
            $mail->line("**Localisation :** {$devis->location}");
        }

        $mail->line('**Matériau :** '.($devis->product?->name ?? 'À définir'));

        if ($application) {
            $mail->line("**Pose :** {$application}");
        }

        if ($devis->surface_m2) {
            $mail->line('**Surface :** '.rtrim(rtrim(number_format((float) $devis->surface_m2, 2, ',', ' '), '0'), ',').' m²');
        }

        if ($devis->estimated_total) {
            $mail->line('**Estimation :** '.number_format((float) $devis->estimated_total, 0, ',', ' ').' DH');
        }

        if ($devis->message) {
            $mail->line('**Message :** '.$devis->message);
        }

        return $mail
            ->action('Ouvrir la demande', url("/admin/devis/{$devis->getKey()}/edit"))
            ->salutation('— Site '.config('company.name'));
    }
}
