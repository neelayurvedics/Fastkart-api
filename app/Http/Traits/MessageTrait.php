<?php

namespace App\Http\Traits;

use Exception;
use App\Messages\Twilio;
use App\Enums\SMSMethod;
use App\GraphQL\Exceptions\ExceptionHandler;

trait MessageTrait
{

  public function sendMessage($message, $message_method)
  {
    try {

      switch ($message_method) {
        case SMSMethod::TWILIO:
          return Twilio::getIntent($message);

        default:
          throw new Exception(__('errors.invalid_sms_message_method'), 400);
      }
    } catch (Exception $e) {

      throw new ExceptionHandler($e->getMessage(), $e->getCode());
    }
  }
}
