<?php
/**
 * Created by PhpStorm.
 * User: Daniel Reis
 * Date: 27-Jun-19
 * Time: 00:17
 */

namespace App\Entities;


use App\Services\TWService;
use Carbon\Carbon;

class Player
{
    public $id;

    public $name;

    public $rank;

    public $points;

    public $startedAt;

    public $villagesCount;

    public $village;

    public $data;

    public function __construct(TWService $data)
    {
        $this->data = $data;
        dd($this->data->getData());
        $info = $this->data->getData()['game_data'];
        $startTime = Carbon::createFromTimestamp($info['player']['date_started'],'America/Sao_Paulo');
        $this->id = $info['player']['id'];
        $this->name = $info['player']['name'];
        $this->rank = $info['player']['rank'];
        $this->points = $info['player']['points'];
        $this->startedAt = $startTime->toDateTimeString() . " (" . $startTime->diffInDays(Carbon::now()) . " days playing)";
        $this->villagesCount = $info['player']['villages'];
        $this->village = new Village($this->data,$info['village']);
    }

    public function setPlayerData(){
        $data = 1;

    }



}
