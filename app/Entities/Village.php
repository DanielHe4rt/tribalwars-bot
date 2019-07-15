<?php
/**
 * Created by PhpStorm.
 * User: Daniel Reis
 * Date: 27-Jun-19
 * Time: 01:11
 */

namespace App\Entities;


use App\Services\TWService;

class Village
{
    public $client;

    public $id;

    public $name;

    public $displayName;

    public $wood;
    public $woodProd;

    public $stone;
    public $stoneProd;

    public $iron;
    public $ironProd;

    public $population;

    public $maxPopulation;

    public $storage;

    public $coordX;
    public $coordY;

    public function __construct(TWService $data, array $info)
    {
        $this->client = $data;

        $coords = explode('|',$info['coord']);
        $this->coordX = $coords[0];
        $this->coordY = $coords[1];

        $this->id = $info['id'];
        $this->name = $info['name'];
        $this->displayName = $info['display_name'];

        $this->wood = $info['wood'];
        $this->woodProd = $info['wood_prod'];
        $this->stone = $info['stone'];
        $this->stoneProd = $info['stone_prod'];
        $this->iron = $info['iron'];
        $this->ironProd = $info['iron_prod'];

        $this->population = $info['pop'];
        $this->maxPopulation = $info['pop_max'];


        $this->storage = $info['storage_max'];
        $this->storageMaxTimer = [
            $this->getMaterialStorageTimer($this->iron, $this->ironProd),
            $this->getMaterialStorageTimer($this->wood, $this->woodProd),
            $this->getMaterialStorageTimer($this->stone, $this->stoneProd)
        ];
        arsort($this->storageMaxTimer);

        $this->storageMaxTimer = gmdate('H:i:s',reset($this->storageMaxTimer));
    }

    public function getMaterialStorageTimer($material, $materialPerSecond)
    {
        $seconds = 0;
        $timer = $material;
        $status = true;
        while ($status) {
            $seconds++;
            $timer += $materialPerSecond;
            if ($this->storage <= $timer) {
                $status = false;
            }
        }
        return $seconds;
    }

    public function getBuildings()
    {
        $data = $this->client->getBuildingsInformations();
        foreach ($data as $key => $building) {
            if ($building['woodReq'] >= $this->wood) {
                $data[$key]['canBuild'] = false;
//                $data[$key]['woodMissing'] = ($this->wood - $data[$key]['woodReq']);
            }

            if ($building['ironReq'] >= $this->iron) {
                $data[$key]['canBuild'] = false;
//                $data[$key]['ironMissing'] = ($this->iron - $data[$key]['ironReq']);
            }

            if ($building['stoneReq'] >= $this->stone) {
                $data[$key]['canBuild'] = false;
//                $data[$key]['stoneMissing'] = ($this->stone - $data[$key]['stoneReq']) ;
            }
        }
        return $data;
    }

    public function build(string $build)
    {
        return $this->client->queueBuilding($build);
    }

    public function recruit(string $unity, $quantity){
        return $this->client->queueUnits($unity,$quantity);
    }

    public function getNearestRaidableVillages(){
        $chunks  = $this->client->getMap($this->coordX,$this->coordY)[0]['data']['villages'];
        $villages = [];
        $raidableVillage = [];
        foreach($chunks as $chunk){
            foreach($chunk as $village){
                $villages[] = $village;
                if(!$village[2]){
                    $village['coords'] = $this->client->getEnemyVillageCoordinate($village[0]);
                    $this->client->attackVillage($this->id,$village['coords'],[]);
                    $raidableVillage[] = $village;
                }
            }
        }

        return $raidableVillage;
    }
}

