<?php
/**
 * src/pocketmine/block/MobSpawner.php
 *
 * @package default
 */
/*
 *
 *  _                       _           _ __  __ _
 * (_)                     (_)         | |  \/  (_)
 *  _ _ __ ___   __ _  __ _ _  ___ __ _| | \  / |_ _ __   ___
 * | | '_ ` _ \ / _` |/ _` | |/ __/ _` | | |\/| | | '_ \ / _ \
 * | | | | | | | (_| | (_| | | (_| (_| | | |  | | | | | |  __/
 * |_|_| |_| |_|\__,_|\__, |_|\___\__,_|_|_|  |_|_|_| |_|\___|
 *                     __/ |
 *                    |___/
 *
 * This program is a third party build by ImagicalMine.
 *
 * PocketMine is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Lesser General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * @author ImagicalMine Team
 * @link http://forums.imagicalmine.net/
 *
 *
*/
namespace pocketmine\block;
use pocketmine\item\Item;
use pocketmine\item\Tool;
use pocketmine\nbt\NBT;
use pocketmine\nbt\tag\CompoundTag;
use pocketmine\nbt\tag\ListTag;
use pocketmine\nbt\tag\IntTag;
use pocketmine\nbt\tag\StringTag;
use pocketmine\Player;
use pocketmine\tile\Tile;
use pocketmine\tile\Spawnable;
class MonsterSpawner extends Solid
{
    protected $id = self::MONSTER_SPAWNER;
    /**
     *
     * @param unknown $meta (optional)
     */
    public function __construct($meta = 0)
    {
        $this->meta = $meta;
    }
    /**
     *
     * @return unknown
     */
    public function getName()
    {
        return "Monster Spawner";
    }/*
     *
     * @return unknown
     */
    public function getHardness()
    {
        return 3.5;
    }
    /**
     *
     * @return unknown
     */
    public function getToolType()
    {
        return Tool::TYPE_PICKAXE;
    }
    /**
     *
     * @return unknown
     */
    public function getLightLevel()
    {
        return 2;
    }
    /**
     *
     * @param Item    $item
     * @param Block   $block
     * @param Block   $target
     * @param unknown $face
     * @param unknown $fx
     * @param unknown $fy
     * @param unknown $fz
     * @param Player  $player (optional)
     * @return unknown
     */
    public function place(Item $item, Block $block, Block $target, $face, $fx, $fy, $fz, Player $player = null)
    {
        $this->meta = $faces[$player instanceof Player ? $player->getDirection() : 0];
        $this->getLevel()->setBlock($block, $this, true, true);
        $nbt = new CompoundTag("", [
                new ListTag("Items", []),
                new StringTag("id", Tile::MOB_SPAWNER),
                new IntTag("x", $this->x),
                new IntTag("y", $this->y),
                new IntTag("z", $this->z)
            ]);
        $nbt->Items->setTagType(NBT::TAG_Compound);
        if ($item->hasCustomName()) {
            $nbt->CustomName = new StringTag("CustomName", $item->getCustomName());
        }
        if ($item->hasCustomBlockData()) {
            foreach ($item->getCustomBlockData() as $key => $v) {
                $nbt->{$key} = $v;
            }
        }
        return true;
    }
    /**
     *
     * @param Item    $item
     * @param Player  $player (optional)
     * @return unknown
     */
    public function onActivate(Item $item, Player $player = null)
    {
        if ($player instanceof Player) {
            $t = $this->getLevel()->getTile($this);
            $mobspawner = false;
            if ($t instanceof MobSpawner) {
                $mobspawner = $t;
            } elseif($player->getInventory()->getItemInHand()->getId() === 383) {
                $nbt = new CompoundTag("", [
                        new ListTag("SpawnPotentials", [
                        new IntTag("Type", $player->getInventory()->getItemInHand()->getDamage()),
                        new IntTag("Weight", 1),
                        ]),
                        new StringTag("id", Tile::MOB_SPAWNER),
                        new StringTag("SpawnCount", 5),
                        new StringTag("SpawnRange", 4),
                        new IntTag("x", $this->x),
                        new IntTag("y", $this->y),
                        new IntTag("z", $this->z)
                    ]);
                $nbt->Items->setTagType(NBT::TAG_Compound);
                $mobspawner = Tile::createTile("MobSpawner", $this->getLevel()->getChunk($this->x >> 4, $this->z >> 4), $nbt);
            }
            if ($player->isCreative()) {
                return true;
            }
        }
        return true;
    }
    /**
     *
     * @param Item    $item
     * @return unknown
     */
    public function getDrops(Item $item)
    {
        $drops = [];
        if ($item->isPickaxe() >= 1) {
            $drops[] = [0, 0, 1];
        }
        return $drops;
    }
}
