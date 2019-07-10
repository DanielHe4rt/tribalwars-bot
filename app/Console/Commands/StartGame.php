<?php

namespace App\Console\Commands;

use App\Entities\Player;
use App\Enums\Choices;
use App\Services\TWService;
use Illuminate\Console\Command;

class StartGame extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'game:start';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Inicia o Jogo';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $this->info('Welcome to the TribalWars Command Player');
        $this->info('First we need to log in into your account');
        $username = $this->ask('Username');
        $password = $this->secret('Password');

        $service = new TWService();

        if (!$service->login($username, $password)) {
            $this->warn('Your credentials are incorrect. Please restart the aplication.');
            die();
        }
        $worlds = $service->getWorlds();
        $worldsLabel = [];
        foreach ($worlds as $world) {
            $worldsLabel[] = $world['id'] . ($world['playable'] ? " JOIN" : " NEW");
        }

        $data = $this->choice('Select one World', $worldsLabel);

        $world = explode(' ', $data)[0];
        $service->setWorld($world);


        $gameOn = true;
        while ($gameOn) {

            $player = new Player($service);
            $this->info('Game Informations');

            $this->table(
                ['id', 'name', 'rank', 'points', 'startedAt'],
                [
                    [
                        'id' => $player->id,
                        'name' => $player->name,
                        'rank' => $player->rank,
                        'points' => $player->points,
                        'started_at' => $player->startedAt,

                    ]
                ]
            );

            $this->table(
                ['wood', 'stone', 'iron', 'population', 'storageFull'],
                [
                    [
                        'wood' => $player->village->wood . " (" . round($player->village->woodProd * 60 * 60) . " / min)",
                        'stone' => $player->village->stone . " (" . round($player->village->stoneProd * 60 * 60) . " / min)",
                        'iron' => $player->village->iron . " (" . round($player->village->ironProd * 60 * 60) . " / min)",
                        'population' => $player->village->population . "/" . $player->village->maxPopulation,
                        'storageFull' => $player->village->storageMaxTimer
                    ]
                ]
            );

            $this->table(
                ['Building', 'Level', 'Wood Required', 'Stone Required', 'Iron Required', 'Build Time', 'Population Required', 'Can Build'],
                $player->village->getBuildings()
            );
            $menuCommand = $this->choice('What you want to do?', ['Build -> Upgrade your buildings', 'Recruit -> Recruit any type of Unitys', 'Forge -> Create or upgrade your Units', 'Refresh -> Refresh the game', 'Exit -> Leave the Session'], 0);
            $option = explode(' ', $menuCommand)[0];

            switch ($option) {
                case Choices::BUILD:
                    $this->buildOptions($player);
                    break;
                case Choices::RECRUIT:
                    $this->recruitOptions($player);
                    break;
                case Choices::FORGE:
                    break;
                case Choices::REFRESH:
                    break;
                case Choices::EXIT:
                    $gameOn = false;
                    break;
                default:
                    echo 1;
                    break;
            }

        }
    }

    public function buildOptions(Player $player)
    {
        $this->info('Building Options');
        $buildings = $player->village->getBuildings();
        $this->table(
            ['Building', 'Level', 'Wood Required', 'Stone Required', 'Iron Required', 'Build Time', 'Population Required', 'Can Build'],
            $buildings
        );
        $data = [];

        foreach ($buildings as $building) {
            if (!$building['canBuild']) {
                continue;
            }
            $data[] = $building['id'] . " => Level " . $building['level'] . " to " . ($building['level'] + 1);

        }
        $data[] = "Back";

        $buildCmd = $this->choice('What you want to build?', $data);
        if ($buildCmd == "Back") {
            return true;
        }
        $option = explode(' ', $buildCmd)[0];
        $data = $player->village->build($option);

        $this->info("The build '" . $option . "' is on the queue. ");

        if(array_key_exists('error',$data)){
            foreach($data['error'] as $value){
                $this->error($value);
            }
        }
        $this->info("Refreshing...");
        sleep(2);
    }

    public function recruitOptions(Player $player)
    {
        $player->village->recruit('axe', 5);
    }
}
