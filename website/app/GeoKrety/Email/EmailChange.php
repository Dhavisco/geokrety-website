<?php

namespace GeoKrety\Email;

use GeoKrety\Model\EmailActivationToken;
use GeoKrety\Model\User;
use GeoKrety\Service\Smarty;

class EmailChange extends Base {
    protected function setFromDefault() {
        $this->setFromSupport();
    }

    public function sendEmailChangeNotification(EmailActivationToken $token) {
        Smarty::assign('token', $token);

        $this->sendEmailChangeNotificationToOldEmail($token);
        $this->sendEmailChangeNotificationToNewEmail($token);
    }

    protected function sendEmailChangeNotificationToOldEmail(EmailActivationToken $token) {
        if (is_null($token->user->email)) {
            return;
        }
        $this->setTo($token->user);
        $this->setSubject('📯 '._('Changing your email address'));

        if (!$this->send(Smarty::fetch('email-change-to-old-address.html'))) {
            \Flash::instance()->addMessage(_('An error occurred while sending the confirmation mail.'), 'danger');
        }
    }

    protected function sendEmailChangeNotificationToNewEmail(EmailActivationToken $token) {
        $this->setTo($token->email);
        $this->setSubject('✉️ '._('Changing your email address'));

        if (!$this->send(Smarty::fetch('email-change-to-new-address.html'))) {
            \Flash::instance()->addMessage(_('An error occurred while sending the confirmation mail.'), 'danger');
        }
    }

    public function sendEmailChangedNotification(EmailActivationToken $token) {
        Smarty::assign('token', $token);

        $this->sendEmailChangedNotificationToOldEmail($token);
        $this->sendEmailChangedNotificationToNewEmail($token);
    }

    protected function sendEmailChangedNotificationToOldEmail(EmailActivationToken $token) {
        if (is_null($token->previous_email)) {
            return;
        }
        $this->setTo($token->_previous_email);
        $this->setSubject('📯 '._('Email address changed'));

        if (!$this->send(Smarty::fetch('email-address-changed-to-old-address.html'))) {
            \Flash::instance()->addMessage(_('An error occurred while sending the confirmation mail.'), 'danger');
        }
    }

    protected function sendEmailChangedNotificationToNewEmail(EmailActivationToken $token) {
        $this->setTo($token->email);
        $this->setSubject('✉️ '._('Email address changed'));

        if (!$this->send(Smarty::fetch('email-address-changed-to-new-address.html'))) {
            \Flash::instance()->addMessage(_('An error occurred while sending the confirmation mail.'), 'danger');
        }
    }

    public function sendEmailRevertedNotification(User $user) {
        $this->setTo($user);
        $this->setSubject('📯 '._('Email address reverted'));

        if (!$this->send(Smarty::fetch('email-address-reverted.html'))) {
            \Flash::instance()->addMessage(_('An error occurred while sending the confirmation mail.'), 'danger');
        }
    }
}
