<?php
/**
 * Created by PhpStorm.
 * User: Daniel Reis
 * Date: 26-Jun-19
 * Time: 23:49
 */

namespace App\Services;


use GuzzleHttp\Client;

class TWService
{
    public $client;

    public $gameUri;
    public $mapUri;

    public $postKey;

    public $cookie;

    public $world;

    public $brAuth;

    public function __construct()
    {

        $this->gameUri = "/game.php?";
        $this->mapUri = "/map.php?";
        $this->client = new Client([
            // Base URI is used with relative requests
            // You can set any number of default request options.
            'cookies' => true,
            'timeout' => 5.0,
            'allow_redirects' => false,
            'headers' => [
                'TribalWars-Ajax' => 1,
                'X-Requested-With' => 'XMLHttpRequest',
                'Cookie' => $this->cookie
            ]
        ]);

    }

    public function getData()
    {
        $uri = "https://" . $this->world . ".tribalwars.com.br" . $this->gameUri . "screen=api&ajax=resouces";
        echo $this->cookie;
        $response = $this->client->request('GET', $uri, [
            'headers' => [
                'Cookie' => $this->cookie,
                'TribalWars-Ajax' => 1,
                'X-Requested-With' => 'XMLHttpRequest',
            ]
        ]);

        return json_decode($response->getBody(), true);
    }

    public function queueBuilding(string $build)
    {
        $uri = "https://" . $this->world . ".tribalwars.com.br" . $this->gameUri . "screen=main&ajaxaction=upgrade_building&id=" . $build . "&type=main&h=" . $this->postKey;

        $response = $this->client->request('POST', $uri);


        return json_decode($response->getBody(), true);
    }

    public function getBuildingsInformations()
    {
        $uri = $uri = "https://" . $this->world . ".tribalwars.com.br" . $this->gameUri . "screen=main&action=cancel&id=1&type=main&h=" . $this->postKey;

        $response = $this->client->request('POST', $uri);
        $data = $response->getBody();

        $data = explode('BuildingMain.buildings = ', $data);
        $data = explode('</script>', $data[1])[0];
        $data = substr($data, 0, -2);
        $data = preg_replace('/\s/', '', $data);

        $data = json_decode($data, true);
        $buildings = [];

        foreach ($data as $key => $building) {
            $buildings[$key]['id'] = $building['id'];
            $buildings[$key]['level'] = $building['level'];
            $buildings[$key]['woodReq'] = $building['wood'];
            $buildings[$key]['stoneReq'] = $building['stone'];
            $buildings[$key]['ironReq'] = $building['iron'];
            $buildings[$key]['buildTime'] = round($building['build_time'] / 60) . " minutes";
            $buildings[$key]['points'] = $building['points'];
            if ($building['id'] == "church_f" || $building['id'] == "place" || $building['id'] == "statue") {
                $buildings[$key]['canBuild'] = false;
            } else {
                $buildings[$key]['canBuild'] = true;
            }

        }
        return $buildings;

    }

    public function queueUnits(string $unitType, int $quantity)
    {
        $uri = $uri = "https://" . $this->world . ".tribalwars.com.br" . $this->gameUri . "screen=barracks&ajaxaction=train&mode=train&h=" . $this->postKey;
        $response = $this->client->request('POST', $uri, [
            'form_params' => [
                sprintf('units[%s]', $unitType) => $quantity
            ]
        ]);

        return json_decode($response->getBody(), true);

    }

    public function getQueueBuilds()
    {
        $uri = $uri = "https://" . $this->world . ".tribalwars.com.br" . $this->gameUri . "screen=main";
        $response = $this->client->request('GET', $uri);

        $data = $response->getBody();


        // Building Queue if exists
        $queue = [];
        if (strpos($data, 'id="buildqueue"') !== false) {
            $data = str_replace(['nowrap'], '', $data);
            $exp = explode('<td class="lit-item">', $data);
            array_shift($exp);

            foreach ($exp as $key => $value) {

                $queue[] = explode('</td>', $value)[0];
            }
        }


        return $data;

    }

    public function getPostkey()
    {
        $uri = $uri = "https://" . $this->world . ".tribalwars.com.br" . $this->gameUri . "screen=main";
        $response = $this->client->request('GET', $uri);
        $data = $response->getBody();

        preg_match('/(h=)\w+/', $data, $result);
        $this->postKey = substr($result[0], 2);

        return $data;
    }

    public function getBarracksInformation()
    {
        $uri = $uri = "https://" . $this->world . ".tribalwars.com.br" . $this->gameUri . "screen=barracks";
        $response = $this->client->request('GET', $uri);

        $data = $response->getBody();
        $data = explode('unit_managers.units = ', $data);
        $data = explode(';', $data[1])[0];

        $original = preg_replace('/\s/', '', $data);
        $data = str_replace(['{', '}'], '', $original);
        $data = explode(':', $data);
        $pushed = [];
        foreach ($data as $key => $value) {
            if (strpos($value, ',') !== false) {
                $value = explode(',', $value)[1];
            }

            if (in_array($value, $pushed)) continue;


            array_push($pushed, $value);
            $original = str_replace($value, sprintf('"%s"', $value), $original);

        }
        $data = json_decode($original, true);

        return $data;

    }

    public function login(string $username, string $password)
    {
        $uri = "https://www.tribalwars.com.br/page/auth";
        $response = $this->client->request('POST', $uri, [
            'form_params' => [
                'username' => $username,
                'password' => $password
            ]
        ]);

        return strpos($response->getBody(), 'error') !== false ? false : true;
    }

    public function getWorlds()
    {
        $uri = "https://www.tribalwars.com.br";
        $response = $this->client->request('GET', $uri);
        $original = $response->getBody();

        $data = explode('<a class="world-select', $original);
        array_shift($data);
        $worlds = [];
        foreach ($data as $key => $value) {
            $world = explode('</a>', $value)[0];
            $worldHref = explode(' href="', $world);
            $worldHref = explode('"', $worldHref[1])[0];
            $worlds[$key]['href'] = $worldHref;
            $worlds[$key]['id'] = strtoupper(str_replace('/page/play/', '', $worldHref));
            $worlds[$key]['playable'] = strpos($world, 'world_button_active') !== false ? true : false;
        }

        return $worlds;
    }

    public function setWorld(string $world)
    {
        $this->world = strtolower($world);

        $uri = "https://www.tribalwars.com.br/page/play/" . $this->world;
        $response = $this->client->request("POST", $uri);
        $data = json_decode($response->getBody(), true);

        $response = $this->client->request("POST", $data['uri']);

        $this->getPostkey();
        return $response;

    }

    public function getMarketRates()
    {
        $uri = "https://" . $this->world . ".tribalwars.com.br" . $this->gameUri . "4&screen=market&ajax=exchange_data";
        $response = $this->client->request("GET", $uri);
        return json_decode($response->getBody(), true);
    }

    public function getMap(int $x, int $y)
    {
        $uri = "https://" . $this->world . ".tribalwars.com.br" . $this->mapUri . "$x" . "_" . $y . "=0";

        $response = $this->client->request("GET", $uri);
        return json_decode($response->getBody(), true);
    }

    public function getEnemyVillageCoordinate(int $villageId)
    {
        $uri = "https://" . $this->world . ".tribalwars.com.br" . $this->gameUri . "screen=info_village&id=" . $villageId;

        $response = $this->client->request("GET", $uri);
        $body = $response->getBody();
        $exp = explode('Coordenadas:</td><td>', $body);
        $coords = [];
        if (count($exp) > 0) {
            $exp = explode('</td></tr>', $exp[1]);
            $coord = explode('|', $exp[0]);
            $coords['x'] = $coord[0];
            $coords['y'] = $coord[1];
        }

        return $coords;
    }

    public function attackVillage(int $villageId, array $coord, array $units, $action = "attack")
    {
        $uri = "https://" . $this->world . ".tribalwars.com.br" . $this->gameUri . "village=$villageId&screen=place";
        $formData = [];
        $response = $this->client->request("GET", $uri);
        $data =  $response->getBody();
        preg_match_all('/<input type="hidden" [^>]*? value="(.*)"/', $data, $data);
        foreach($data[0] as $input){
            preg_match_all('/"(.*?)"/',$input,$input);
            foreach($input[0] as $key => $value){
                $input[0][$key] = str_replace('"','',$value);
            }
            $formData[$input[0][1]] = $input[0][2];
        }

        $formData['spear'] = 5;
        $formData[$action] = '';

        $formData = array_merge($formData,$coord);

        $uri = "https://" . $this->world . ".tribalwars.com.br" . $this->gameUri . "village=$villageId&screen=place&ajax=confirm";
        $response = $this->client->request("POST", $uri,[
            'form_params' => $formData
        ]);
        $data = json_decode($response->getBody(),true);


        $data = $data['response']['dialog'];
        preg_match_all('/<input type="hidden" [^>]*? value="(.*)"/',$data,$inputs);

        foreach($inputs[0] as $input){
            preg_match_all('/"(.*?)"/',$input,$data);
            foreach($data[0] as $key => $value){
                $inputs[0][$key] = str_replace('"','',$value);
            }

        }
        dd($inputs);

    }

}
