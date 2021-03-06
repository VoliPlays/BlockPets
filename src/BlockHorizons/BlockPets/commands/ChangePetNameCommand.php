<?php

namespace BlockHorizons\BlockPets\commands;

use BlockHorizons\BlockPets\Loader;
use pocketmine\command\CommandSender;
use pocketmine\Player;
use pocketmine\utils\TextFormat as TF;

class ChangePetNameCommand extends BaseCommand {

	public function __construct(Loader $loader) {
		parent::__construct($loader, "changepetname", "Changes the name of a pet", "/changepetname <pet name> <new name> [player]", ["cpn, chpn"]);
		$this->setPermission("blockpets.command.changepetname.use");
	}

	public function execute(CommandSender $sender, $commandLabel, array $args): bool {
		if(!$this->testPermission($sender)) {
			$this->sendPermissionMessage($sender);
			return true;
		}

		if(!$sender instanceof Player) {
			$this->sendConsoleError($sender);
			return true;
		}
		$newName = $args[1];

		if(isset($args[2])) {
			if($sender->hasPermission("blockpets.command.changepetname.others")) {
				$this->sendPermissionMessage($sender);
				return true;
			}
			if(($player = $this->getLoader()->getServer()->getPlayer($args[2])) === null) {
				$this->sendWarning($sender, $this->getLoader()->translate("commands.errors.player.not-found"));
				return true;
			}
			if(($pet = $this->getLoader()->getPetByName($args[0], $player)) === null) {
				$this->sendWarning($sender, $this->getLoader()->translate("commands.errors.player.no-pet-other"));
				return true;
			}
			$oldName = $pet->getPetName();
			$pet->changeName($newName);
			$sender->sendMessage(TF::GREEN . $this->getLoader()->translate("commands.changepetname.success", [
					$oldName,
					$newName
				]));
			return true;
		} else {
			if(($pet = $this->getLoader()->getPetByName($args[0], $sender)) === null) {
				$this->sendWarning($sender, $this->getLoader()->translate("commands.errors.player.no-pet"));
				return true;
			}
		}

		$oldName = $pet->getPetName();
		$pet->changeName($newName);
		$sender->sendMessage(TF::GREEN . $this->getLoader()->translate("commands.changepetname.success", [
				$oldName,
				$newName
			]));
		return true;
	}
}
