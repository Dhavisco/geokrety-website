<?php

namespace GeoKrety\Controller;

use Event;
use Flash;
use GeoKrety\Email\EmailChange;
use GeoKrety\Model\EmailActivationToken;
use GeoKrety\Service\Smarty;

class UserEmailChangeRevertToken extends Base {
    /**
     * @var EmailActivationToken
     */
    private $token;

    public function beforeRoute(\Base $f3) {
        parent::beforeRoute($f3);

        $token = new EmailActivationToken();

        // Check database for provided token
        if ($f3->exists('PARAMS.token')) {
            $token->load(['revert_token = ? AND used = ? AND created_on_datetime > NOW() - cast(? as interval)', $f3->get('PARAMS.token'), EmailActivationToken::TOKEN_CHANGED, GK_SITE_EMAIL_REVERT_CODE_DAYS_VALIDITY.' DAY']);
            if ($token->dry()) {
                Flash::instance()->addMessage(_('Sorry this token is not valid, already used or expired.'), 'danger');
                $f3->reroute('@user_update_email_validate');
            }
            $token->token = $f3->get('PARAMS.token');
        }

        $this->token = $token;
        Smarty::assign('token', $this->token);
    }

    public function post(\Base $f3) {
        $f3->get('DB')->begin();

        // Check the wanted action
        if ($f3->get('POST.validate') === 'true') {
            $this->accept();
        } elseif ($f3->get('POST.validate') === 'false') {
            $this->refuse($f3);
        } else {
            Flash::instance()->addMessage(_('Unexpected value.'), 'danger');
            $this->get($f3);
            die();
        }

        $this->token->touch('reverted_on_datetime');
        $this->token->reverting_ip = \Base::instance()->get('IP');
        if (!$this->token->validate()) {
            $this->get($f3);
            die();
        }

        $this->token->user->save();
        $this->token->save();

        if ($f3->get('ERROR')) {
            Flash::instance()->addMessage(_('Something went wrong, operation aborted.'), 'danger');
            $this->get($f3);
            die();
        }

        $f3->get('DB')->commit();
        Event::instance()->emit('email.token.used', $this->token);

        // Notifications
        if ($f3->get('POST.validate') === 'true') {
            Flash::instance()->addMessage(_('Perfect! Enjoy your new email address.'), 'success');
        } else {
            $smtp = new EmailChange();
            $smtp->sendEmailRevertedNotification($this->token->user);
            Flash::instance()->addMessage(_('Your email address has been reverted.'), 'success');
            Event::instance()->emit('user.email.changed', $this->token->user);
        }

        $f3->reroute(sprintf('@user_details(@userid=%d)', $this->token->user->id));
    }

    public function accept() {
        // Mark token as used
        $this->token->used = EmailActivationToken::TOKEN_VALIDATED;
    }

    public function refuse(\Base $f3) {
        // Mark token as used
        $this->token->used = EmailActivationToken::TOKEN_REVERTED;
        $this->token->user->_email = $this->token->previous_email;

        if (!$this->token->user->validate()) {
            $this->get($f3);
            die();
        }
    }

    public function get(\Base $f3) {
        // Reset eventual transaction
        if ($f3->get('DB')->trans()) {
            $f3->get('DB')->rollback();
        }
        Smarty::render('pages/email_change_revert_token.tpl');
    }
}
