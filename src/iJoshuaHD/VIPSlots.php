<?php

namespace iJoshuaHD;

use pocketmine\event\Listener;

use pocketmine\Player;
use pocketmine\plugin\PluginBase;

use pocketmine\event\player\PlayerKickEvent;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\utils\Config;

class VIPSlots extends PluginBase implements Listener{
	/** @var Config */
	private $vips;

    public function onEnable(){
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		
		$this->loadVIPSList();
		
		$this->getLogger()->info("VIPSlots Enabled!");
	}
    
    public function onDisable(){
		$this->getLogger()->info("VIPSlots Disabled!");
    }

	/*****************
	*================*
	*===[ Events ]===*
	*================*
	*****************/

	public function onPlayerKick(PlayerKickEvent $event){
		if($this->vips->exists(strtolower($event->getPlayer()->getName())) or ($event->getPlayer()->hasPermission("vips.*") or $event->getPlayer()->hasPermission("vips.slot")) and $event->getReason() === "server full"){
			$event->setCancelled(true);
		}
	}


	/*****************
	*================*
	*==[ Commands ]==*
	*================*
	*****************/
	
	public function onCommand(CommandSender $p, Command $command, $label, array $args){
	
		if($command->getName() == "vips"){
		
			if(!isset($args[0]) || count($args) > 2){
				$p->sendMessage("Usage: /vips <add/remove/list>");
				return true;
			}
			
			switch(strtolower($args[0]))
			{
				case "add":
				
					if(isset($args[1])){
						$who_player = $this->getValidPlayer($args[1]);
						
						if($who_player instanceof Player){
							$target = $who_player->getName();
						}
						else{
							$target = $args[1];
						}
						
						if($this->addPlayer($target)){
							$p->sendMessage("Successfully added '$target' on VIPSlots!");
						}
						else{
							$p->sendMessage("$target is already added on VIPSlots!");
						}
					}
					else{
						$p->sendMessage("Usage: /vips add <player>");
					}
				
					break;
					
				case "remove":
				
					if(isset($args[1])){
						$who_player = $this->getValidPlayer($args[1]);
						
						if($who_player instanceof Player){
							$target = $who_player->getName();
						}
						else{
							$target = $args[1];
						}
						
						if($this->removePlayer($target)){
							$p->sendMessage("Successfully removed '$target' on VIPSlots!");
						}
						else{
							$p->sendMessage("$target doesn't exist on VIPSlots!");
						}
					}
					else{
						$p->sendMessage("Usage: /vips remove <player>");
					}
					
					break;
					
				case "list":

					$vips = $this->vips->getAll();

					if(count($vips) < 1){
						$m = " - There are no players in the VIPSlots list -";
					}else{
						$m = "-==[ VIPSlots List ]==-\n";
						foreach ($vips as $k => $v){
							$m .= " - " . $k;
						}
					}
					$p->sendMessage($m);
				
					break;
					
				default:
				
					$p->sendMessage("Usage: /vips <add/remove/list>");
					
					break;
					
			}
			
			return true;
		}
	}
	
	/*****************
	*================*
	*==[ Non-APIs ]==*
	*================*
	*****************/
	
	private function loadVIPSList(){
		if(!is_dir($this->getDataFolder())){
			mkdir($this->getDataFolder());
		}
		$this->vips = new Config($this->getDataFolder() . "vip_players.txt", Config::ENUM);
	}
	
	private function getValidPlayer($target){
		$player = $this->getServer()->getPlayer($target);
		return $player instanceof Player ? $player : $this->getServer()->getOfflinePlayer($target);
	}
	
	/*****************
	*================*
	*==[   APIs   ]==*
	*================*
	*****************/
	
	public function addPlayer($player){
		$target = $this->getValidPlayer($player);
		
		if($target instanceof Player){
			$p = strtolower($target->getName());
		}
		else{
			$p = strtolower($player);
		}
		
		if($this->vips->exists($p)) return false;
		
		$this->vips->set($p, true);
		$this->vips->save();
			
		return true;
	}
	
	public function removePlayer($player){
	
		$target = $this->getValidPlayer($player);
		
		if($target instanceof Player){
			$p = strtolower($target->getName());
		}
		else{
			$p = strtolower($player);
		}
	
		if(!$this->vips->exists($p)) return false;
		
		$this->vips->remove($p);
		$this->vips->save();
		
		return true;
	}
}
