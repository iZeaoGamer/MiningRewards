<?php

namespace david\miningrewards;

use david\miningrewards\item\Reward;
use pocketmine\event\block\BlockBreakEvent;
use pocketmine\event\Listener;
use pocketmine\level\particle\HugeExplodeSeedParticle;
use pocketmine\level\sound\BlazeShootSound;
use pocketmine\utils\TextFormat;
use pocketmine\event\player\PlayerInteractEvent;

class EventListener implements Listener {

    /** @var Loader */
    private $plugin;

    /**
     * EventListener constructor.
     *
     * @param Loader $plugin
     */
    public function __construct(Loader $plugin) {
        $this->plugin = $plugin;
        $plugin->getServer()->getPluginManager()->registerEvents($this, $plugin);
    }
    public function onInteract(PlayerInteractEvent $event){
        $player = $event->getPlayer();
        $this->player = $player;
        $this->item = $player->getInventory()->getItemInHand();
$amount = mt_rand($this->plugin->getCountMin(), $this->plugin->getCountMax());
        $rewards = $this->plugin->getRewards();
        for($i = 0; $i < $amount; $i++) {
            $reward = $rewards[array_rand($rewards)];
            if(!$reward instanceof Item) {
                return;
          //      $this->item->getLevel()->dropItem($this->item, $reward);
           //     continue;
            }
            $reward = explode(":", $reward);
            $this->plugin->getServer()->dispatchCommand(new ConsoleCommandSender(),
                str_replace("{player}", $this->player->getName(), $reward[0]));
            if(isset($reward[1])) {
                $this->player->sendMessage(str_replace("&", TextFormat::ESCAPE, $reward[1]));
            }
        }
        $this->item->getLevel()->addParticle(new HugeExplodeSeedParticle($this->item));
        $this->item->getLevel()->broadcastLevelSoundEvent($this->item, LevelSoundEventPacket::SOUND_EXPLODE);
        $this->item->flagForDespawn();
    }
}
    /**
     * @param BlockBreakEvent $event
     */
    public function onBlockBreak(BlockBreakEvent $event) {
        if($event->isCancelled()) {
            return;
        }
        if(mt_rand(1, $this->plugin->getChance()) === mt_rand(1, $this->plugin->getChance())) {
            $player = $event->getPlayer();
            $level = $player->getLevel();
            $item = new Reward();
            $lore = [];
            $lore[] = TextFormat::RESET . TextFormat::YELLOW . "Reward Min: " . Loader::getInstance()->getCountMin();
            $lore[] = TextFormat::RESET . TextFormat::YELLOW . "Reward Max: " . Loader::getInstance()->getCountMax();
            $lore[] = "";
            $lore[] = TextFormat::RESET . TextFormat::GRAY . "Click to open reward.";
            $item->setLore($lore);
            $item->setCustomName(TextFormat::RESET . TextFormat::GOLD . TextFormat::BOLD . "Reward");
            $level->dropItem($player, $item);
            $level->addParticle(new HugeExplodeSeedParticle($player));
            $level->addSound(new BlazeShootSound($player));
            $titles = Loader::getTitles();
            $player->addTitle(TextFormat::BOLD . TextFormat::AQUA . $titles[array_rand($titles)],
                TextFormat::GRAY . "You have found a reward from mining!");
        }
    }
}
