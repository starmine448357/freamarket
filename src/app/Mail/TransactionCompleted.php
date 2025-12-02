<?php

namespace App\Mail;

use App\Models\Purchase;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;

class TransactionCompleted extends Mailable
{
    use Queueable, SerializesModels;

    public $purchase;

    /**
     * コンストラクタ（Purchase を注入）
     */
    public function __construct(Purchase $purchase)
    {
        $this->purchase = $purchase;
    }

    /**
     * メール内容の構築
     */
    public function build()
    {
        return $this->subject('取引が完了しました')
            ->markdown('emails.transaction.completed');
    }
}
