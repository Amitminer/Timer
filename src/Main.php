<?php

declare(strict_types=1);

namespace AmitxD\Timer;

use pocketmine\plugin\PluginBase;
use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\scheduler\Task;
use pocketmine\player\Player;
use pocketmine\utils\TextFormat;

class Main extends PluginBase{
    private $timers = [];

    public function onCommand(CommandSender $sender, Command $command, string $label, array $args): bool{
        if(strtolower($command->getName()) === "timer"){
            if(count($args) < 1){
                return false;
            }
            switch(strtolower($args[0])){
                case "add":
                    if(!$sender instanceof Player){
                        $sender->sendMessage(TextFormat::RED . "This command can only be used in-game.");
                        break;
                    }
                    if(count($args) < 2){
                        $sender->sendMessage(TextFormat::RED . "Please specify a time.");
                        break;
                    }
                    $time = $this->parseTime($args[1]);
                    if($time === false){
                        $sender->sendMessage(TextFormat::RED . "Invalid time format.");
                        break;
                    }
                    $this->timers[$sender->getName()] = $time;
                    $sender->sendMessage(TextFormat::GREEN . "Added timer for " . $args[1]);
                    break;
                    
                case "remove":
                        $name = $sender->getName();
                        if(isset($this->timers[$name])){
                            unset($this->timers[$name]);
                            $sender->sendMessage($this->prefix . "Timer removed!");
                        }else{
                            $sender->sendMessage($this->prefix . "You have no active timer!");
                        }
                        break;
                case "check":
                    if(!$sender instanceof Player){
                        $sender->sendMessage(TextFormat::RED . "This command can only be used in-game.");
                        break;
                    }
                    if(!isset($this->timers[$sender->getName()])){
                        $sender->sendMessage(TextFormat::YELLOW . "You do not have an active timer.");
                        break;
                    }
                    $remaining = $this->timers[$sender->getName()] - time();
                    if($remaining <= 0){
                        $sender->sendMessage(TextFormat::YELLOW . "Your timer has expired.");
                        unset($this->timers[$sender->getName()]);
                    }else{
                        $hours = floor($remaining / 3600);
                        $minutes = floor(($remaining % 3600) / 60);
                        $seconds = $remaining % 60;
                        $msg = TextFormat::YELLOW . "Time remaining: ";
                        if($hours > 0){
                            $msg .= "$hours hour(s), ";
                        }
                        if($minutes > 0){
                            $msg .= "$minutes minute(s), ";
                        }
                        $msg .= "$seconds second(s).";
                        $sender->sendMessage($msg);
                    }
                    break;
                default:
                    return false;
            }
            return true;
        }
        return false;
    }

    public function onDisable(): void{
        foreach($this->getServer()->getOnlinePlayers() as $player){
            if(isset($this->timers[$player->getName()])){
                unset($this->timers[$player->getName()]);
            }
        }
    }

    private function parseTime(string $str){
        if(preg_match('/^(\d+)(h|m|s)$/', $str, $matches)){
            switch(strtolower($matches[2])){
                case "h":
                    return time() + intval($matches[1]) * 3600;
                case "m":
                    return time() + intval($matches[1]) * 60;
                case "s":
                    return time() + intval($matches[1]);
            }
        }
        return false;
    }
}
