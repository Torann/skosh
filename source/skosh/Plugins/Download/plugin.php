<?php namespace Plugins\Download;

class Plugin extends \Skosh\Foundation\Plugin
{
    /**
     * File name.
     *
     * @var string
     */
    private $logfile;

    public function fire($app, array $request)
    {
        $this->logfile = $app->getRoot().'/downloads.json';

        if (! empty($request['file']))
        {
            $filename = $request['file'];
            $file_path = $this->getConfig('folder') . '/' . $filename;

            if (file_exists($app->getRoot().$file_path))
            {
                $this->updateLog($filename);
                header("Location: {$file_path}");
            }
            else {
                header("HTTP/1.0 404 Not Found");
                echo '404 Download Not Found';
            }

            exit;
        }
    }

    /**
     * Update stats JSON file
     *
     * @param  string $filename
     * @return bool
     */
    protected function updateLog($filename)
    {
        $entries = array();

        // Get contents
        if (file_exists($this->logfile)) {
            $entries = json_decode(file_get_contents($this->logfile), true);
        }

        // Update count
        if (isset($entries[$filename])) {
            $entries[$filename] = $entries[$filename] + 1;
        }
        else {
            $entries[$filename] = 1;
        }

        // Save
        return (bool) file_put_contents($this->logfile, json_encode($entries));
    }
}
