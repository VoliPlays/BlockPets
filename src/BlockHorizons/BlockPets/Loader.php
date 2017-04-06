<?php

namespace BlockHorizons\BlockPets;

use BlockHorizons\BlockPets\listeners\EventListener;
use BlockHorizons\BlockPets\pets\BasePet;
use BlockHorizons\BlockPets\pets\creatures\BlazePet;
use BlockHorizons\BlockPets\pets\creatures\ChickenPet;
use BlockHorizons\BlockPets\pets\creatures\GhastPet;
use pocketmine\entity\Entity;
use pocketmine\event\entity\EntitySpawnEvent;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\DoubleTag;
use pocketmine\nbt\tag\FloatTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\plugin\PluginBase;
use Sandertv\BlockSniper\commands\SpawnPetCommand;

class Loader extends PluginBase {

	const PETS = [
		"Ghast",
		"Blaze",
		"Chicken"
	];

	const PET_CLASSES = [
		BlazePet::class,
		ChickenPet::class,
		GhastPet::class
	];

	public function onEnable() {
		foreach(self::PET_CLASSES as $petClass) {
			Entity::registerEntity($petClass, true);
		}
		$this->registerCommands();
		$this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
	}

	public function registerCommands() {
		$petCommands = [
			"spawnpet" => new SpawnPetCommand($this)
		];
		foreach($petCommands as $fallBack => $command) {
			$this->getServer()->getCommandMap()->register($fallBack, $command);
		}
	}

	public function onDisable() {

	}

	public function onEntitySpawn(EntitySpawnEvent $event) {
		if($event->getEntity() instanceof BasePet) {
			$clearLaggPlugin = $this->getServer()->getPluginManager()->getPlugin("ClearLagg");
			if($clearLaggPlugin !== null) {
				$clearLaggPlugin->exemptEntity($event->getEntity());
			}
		}
	}

	/**
	 * @param string $entityName
	 *
	 * @return bool
	 */
	public function petExists(string $entityName) {
		foreach(self::PETS as $pet) {
			if(strtolower($pet) === strtolower($entityName)) {
				return true;
			}
		}
		return false;
	}

	/**
	 * @param string $entityName
	 *
	 * @return string|null
	 */
	public function getPet(string $entityName): string {
		if(!$this->petExists($entityName)) {
			return null;
		}
		foreach(self::PETS as $pet) {
			if(strtolower($pet) === strtolower($entityName)) {
				return $pet;
			}
		}
		return null;
	}

	public function createPet(string $entityName, Player $position, float $scale = 1.0) {
		$nbt = new CompoundTag("", [
			"Pos" => new ListTag("Pos", [
				new DoubleTag("", $position->x),
				new DoubleTag("", $position->y),
				new DoubleTag("", $position->z)
			]),
			"Motion" => new ListTag("Motion", [
				new DoubleTag("", 0),
				new DoubleTag("", 0),
				new DoubleTag("", 0)
			]),
			"Rotation" => new ListTag("Rotation", [
				new FloatTag("", $position->yaw),
				new FloatTag("", $position->pitch)
			]),
			"petOwner" => new StringTag("petOwner", $position->getName()),
			"scale" => new FloatTag("scale", $scale),
		]);
		$chunk = $position->level->getChunk($position->x >> 4, $position->z >> 4, true);

		return Entity::createEntity($entityName . "Pet", $chunk, $nbt);
	}
}