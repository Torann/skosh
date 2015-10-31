<?php namespace Plugins\Twitter;

require_once (__DIR__.DIRECTORY_SEPARATOR.'codebird'.DIRECTORY_SEPARATOR.'codebird.php');

class Plugin extends \Skosh\Foundation\Plugin
{
    public function fire($app, array $request)
    {
        // CodeBird Instance
        \Codebird\Codebird::setConsumerKey($this->getConfig('consumer_key'), $this->getConfig('consumer_secret'));
        $cb = \Codebird\Codebird::getInstance();
        $cb->setToken($this->getConfig('access_token'), $this->getConfig('access_token_secret'));
        $cb->setReturnFormat(CODEBIRD_RETURNFORMAT_JSON);

        // Set Values
        $username = $this->getConfig('username');
        $count    = $this->getConfig('count');

        // Get timeline
        return $cb->statuses_userTimeline("screen_name={$username}&count={$count}&exclude_replies=true");
    }
}
