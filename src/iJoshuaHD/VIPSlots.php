<?php

namespace iJoshuaHD;

use pocketmine\event\Listener;

use pocketmine\plugin\PluginBase;

use pocketmine\event\player\PlayerKickEvent;

use pocketmine\command\CommandSender;
use pocketmine\command\Command;

use pocketmine\utils\Config;

class VIPSlots extends PluginBase implements Listener
{
    public function onEnable()
	{
		$this->getServer()->getPluginManager()->registerEvents($this, $this);
		
		$this->loadVIPSList();
		
		$this->getLogger()->info("VIPSlots Enabled!");
	}
    
    public function onDisable()
	{
		$this->getLogger()->info("VIPSlots Disabled!");
    }

	/*****************
	*================*
	*===[ Events ]===*
	*================*
	*****************/

	public function onPlayerKick(PlayerKickEvent $event)
	{
		$p = strtolower($event->getPlayer()->getName());
		
		if($this->vips->exists($p))
		{
			$event->setCancelled(true);
		}
	}


	/*****************
	*================*
	*==[ Commands ]==*
	*================*
	*****************/
	
	public function onCommand(CommandSender $p, Command $command, $label, array $args)
	{
		if($command->getName() == "vips")
		{
			if(!isset($args[0]) || count($args) > 2)
			{
				$p->sendMessage("Usage: /vips <add/remove> <player>");
				
				return true;
			}
			
			switch(strtolower($args[0]))
			{
				case "add":
				
					if(isset($args[1]))
					{
						$who_player = $this->getValidPlayer($args[1]);
						
						if($who_player instanceof Player)
						{
							$target = $who_player->getName();
						}
						else
						{
							$target = $args[1];
						}
						
						if($this->addPlayer($target))
						{
							$p->sendMessage("Successfully added '$target' on VIPSlots!");
						}
						else
						{
							$p->sendMessage("$target is already added on VIPSlots!");
						}
					}
					else
					{
						$p->sendMessage("Usage: /vips add <player>");
					}
				
					break;
					
				case "remove":
				
					if(isset($args[1]))
					{
						$who_player = $this->getValidPlayer($args[1]);
						
						if($who_player instanceof Player)
						{
							$target = $who_player->getName();
						}
						else
						{
							$target = $args[1];
						}
						
						if($this->removePlayer($target))
						{
							$p->sendMessage("Successfully removed '$target' on VIPSlots!");
						}
						else
						{
							$p->sendMessage("$target doesn't exist on VIPSlots!");
						}
					}
					else
					{
						$p->sendMessage("Usage: /vips remove <player>");
					}
					
					break;
				
				default:
				
					$p->sendMessage("Usage: /vips <add/remove>");
					
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
	
	private function loadVIPSList()
	{
		@mkdir($this->getDataFolder(), 0777, true);
		
		$this->vips = new Config($this->getDataFolder() . "vip_players.txt", Config::ENUM, array(
		));
	}
	
	private function getValidPlayer($target)
	{
		$player = $this->getServer()->getPlayer($target);
		
		return $player instanceof Player ? $player : $this->getServer()->getOfflinePlayer($target);
	}
	
	/*****************
	*================*
	*==[   APIs   ]==*
	*================*
	*****************/
	
	public function addPlayer($player)
	{
		$target = $this->getValidPlayer($player);
		
		if($target instanceof Player)
		{
			$p = strtolower($target->getName());
		}
		else
		{
			$p = strtolower($player);
		}
		
		if($this->vips->exists($p)) return false;
		
		$this->vips->set($p, true);
		
		$this->vips->save();
			
		return true;
	}
	
	public function removePlayer($player)
	{
		$target = $this->getValidPlayer($player);
		
		if($target instanceof Player)
		{
			$p = strtolower($target->getName());
		}
		else
		{
			$p = strtolower($player);
		}
	
		if(!$this->vips->exists($p)) return false;
		
		$this->vips->remove($p);
		
		$this->vips->save();
		
		return true;
	}
}
