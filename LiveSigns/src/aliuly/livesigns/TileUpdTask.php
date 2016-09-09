<?php
namespace aliuly\livesigns;

use pocketmine\scheduler\PluginTask;
use pocketmine\plugin\Plugin;
use pocketmine\tile\Sign;
//use pocketmine\network\protocol\TileEntityDataPacket;
use pocketmine\network\protocol\BlockEntityDataPacket;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\StringTag;

class TileUpdTask extends PluginTask{
	/**
	 * @param Plugin	$owner
	 */
	public function __construct(Plugin $owner){
		parent::__construct($owner);
	}
	public function onRun($currentTicks){
		$this->getOwner()->updateVars();
		foreach ($this->getOwner()->getServer()->getLevels() as $lv) {
			if (count($lv->getPlayers()) == 0) continue;
			if ($lv === null) continue; // Skip all if level is not loaded
			foreach ($lv->getTiles() as $tile) {
				if (!($tile instanceof Sign)) continue;
				$sign = $tile->getText();
				$text = $this->getOwner()->getLiveSign($sign);
				if ($text == null) continue;
				$pk = new BlockEntityDataPacket();
				$data = $tile->getSpawnCompound();
				$data->Text1 = new StringTag("Text1",$text[0]);
				$data->Text2 = new StringTag("Text2",$text[1]);
				$data->Text3 = new StringTag("Text3",$text[2]);
				$data->Text4 = new StringTag("Text4",$text[3]);
				$nbt = new NBT(NBT::LITTLE_ENDIAN);
				$nbt->setData($data);
				$pk->x = $tile->getX();
				$pk->y = $tile->getY();
				$pk->z = $tile->getZ();
				$pk->namedtag = $nbt->write();
				foreach ($lv->getPlayers() as $pl) {
					$pl->dataPacket($pk);
				} //foreach Players
			} //foreach Tiles
		} // foreach Levels
	}
}
