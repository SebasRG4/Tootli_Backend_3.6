<?php

namespace App\Mail;

use App\Models\BusinessSetting;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class CustomerWalletSecurityAlertMail extends Mailable
{
    use Queueable, SerializesModels;

    public $userName;
    public $title;
    public $alertMessage;
    public $details;
    public $eventType;

    /**
     * Create a new message instance.
     *
     * @param string $userName
     * @param string $title
     * @param string $alertMessage
     * @param array $details
     * @param string $eventType
     */
    public function __construct(string $userName, string $title, string $alertMessage, array $details = [], string $eventType = 'general')
    {
        $this->userName = $userName;
        $this->title = $title;
        $this->alertMessage = $alertMessage;
        $this->details = $details;
        $this->eventType = $eventType;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $companyName = BusinessSetting::where('key', 'business_name')->value('value') ?? 'Tootli';
        $logo = BusinessSetting::where('key', 'logo')->value('value') ?? null;

        return $this->subject("🔒 Alerta de Seguridad Tootli Wallet: {$this->title}")
            ->view('email-templates.customer-wallet-security-alert', [
                'companyName' => $companyName,
                'logo' => $logo,
                'userName' => $this->userName,
                'title' => $this->title,
                'alertMessage' => $this->alertMessage,
                'details' => $this->details,
                'eventType' => $this->eventType,
            ]);
    }
}
