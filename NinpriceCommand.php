<?php
/**
 * This file is part of the TelegramBot package.
 *
 * (c) Avtandil Kikabidze aka LONGMAN <akalongman@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * Written by Marco Boretto <marco.bore@gmail.com>
 */

namespace Longman\TelegramBot\Commands\UserCommands;

use Longman\TelegramBot\Commands\UserCommand;
use Longman\TelegramBot\Entities\File;
use Longman\TelegramBot\Entities\PhotoSize;
use Longman\TelegramBot\Entities\UserProfilePhotos;
use Longman\TelegramBot\Request;

/**
 * User "/whoami" command
 *
 * Simple command that returns info about the current user.
 */
class NinpriceCommand extends UserCommand
{
    /**
     * @var string
     */
    protected $name = 'ninprice';

    /**
     * @var string
     */
    protected $description = 'Show skidki';

    /**
     * @var string
     */
    protected $usage = '/ninprice';

    /**
     * @var string
     */
    protected $version = '1.1.0';

    /**
     * @var bool
     */
    protected $private_only = true;

    /**
     * Command execute method
     *
     * @return \Longman\TelegramBot\Entities\ServerResponse
     * @throws \Longman\TelegramBot\Exception\TelegramException
     */
    public function execute()
    {
        $message = $this->getMessage();

        //You can use $command as param
        $chat_id = $message->getChat()->getId();
        $user_id = $message->getFrom()->getId();
        $command = $message->getCommand();


        //Custom code
        $string = "https://searching.nintendo-europe.com/ru/select?q=*&fq=type%3AGAME%20AND%20((playable_on_txt%3A%22HAC%22))%20AND%20sorting_title%3A*%20AND%20*%3A*&sort=price_discount_percentage_f%20desc%2C%20price_lowest_f%20desc&start=0&rows=500&wt=json&bf=linear(ms(priority%2CNOW%2FHOUR)%2C1.1e-11%2C0)&json.wrf=nindo.net.jsonp.jsonpCallback_142363_5999999824";

        function getDataFromUrl($url) {
            $ch = curl_init();
            // Set URL and header options
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);

            // Start to capture the output
            ob_start();
            // Excecuting the curl call
            curl_exec($ch);
            // Get the srtout content and clean the buffer
            $content = ob_get_clean();

            // Close the Curl resource
            curl_close($ch);

            return $content;
        }

        $sourcedata = getDataFromUrl($string);

        $search = "nindo.net.jsonp.jsonpCallback_142363_5999999824(";

        $sourcedata = str_replace($search, '', $sourcedata);

        $sourcedata = substr($sourcedata, 0, -2);

        $sourcedata = json_decode($sourcedata, true);

        $outputData = '';

        foreach($sourcedata["response"]["docs"] as $elem) {
            if ($elem["price_has_discount_b"] == true && isset($elem["price_discount_percentage_f"])) {
                if ($elem["price_discount_percentage_f"] >= 50) {
                    $outputData .= "⚠️";
                }
                $outputData .= $elem["title"] . " - " . $elem["price_lowest_f"] . " RUB (-" . $elem["price_discount_percentage_f"] . "%)\n";
                //$i++;
            }
        }
        //End of custom code


        $data = [
            'chat_id' => $chat_id,
            'text'    => $outputData,
        ];

        return Request::sendMessage($data);
    }
}
