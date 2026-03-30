<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class ReinsuranceRequestEmail extends Mailable
{
    use Queueable, SerializesModels;

    public $mailData;
    public $attachmentPath;
    public $attachmentName;

    /**
     * Create a new message instance.
     *
     * @param array $mailData
     * @param string|null $attachmentPath
     * @param string|null $attachmentName
     */
    public function __construct($mailData, $attachmentPath = null, $attachmentName = null)
    {
        $this->mailData = $mailData;
        $this->attachmentPath = $attachmentPath;
        $this->attachmentName = $attachmentName;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $email = $this->subject($this->mailData['subject'])
                      ->view('emails.reinsurance_request')
                      ->with(['content' => $this->mailData['body']]);

        // Attach PDF if provided
        if ($this->attachmentPath && file_exists($this->attachmentPath)) {
            $email->attach($this->attachmentPath, [
                'as' => $this->attachmentName ?: 'Request_Note.pdf',
                'mime' => 'application/pdf',
            ]);
        }

        return $email;
    }
}