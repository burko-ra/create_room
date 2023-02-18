<?php

namespace ConstructionSite\Buildings;

use ConstructionSite\CommonAmenities\CommonAmenityInterface;
use ConstructionSite\Levels\LevelInterface;
use ConstructionSite\Flats\FlatInterface;

class BuildingDecorator implements BuildingInterface, GetAreaInterface, GetFlatsInterface
{
    protected BuildingInterface $building;
    /**
     * @var array<string,CommonAmenityInterface>
     */
    protected $commonAmenities = [];
    protected float $basePricePerSquareMeter;
    protected float $totalPricePerSquareMeter;

    public function __construct(BuildingInterface $building)
    {
        $this->building = $building;
        $this->basePricePerSquareMeter = $building->getPricePerSquareMeter();
    }

    public function addCommonAmenity(CommonAmenityInterface $commonAmenity): void
    {
        $id = $commonAmenity->getId();
        $this->commonAmenities[$id] = $commonAmenity;
        $this->updateTotalPricePerSquareMeter();
    }

    /**
     * @return array<string,CommonAmenityInterface>
     */
    public function getCommonAmenities()
    {
        return $this->commonAmenities;
    }

    public function addLevel(): void
    {
        $this->building->addLevel();
    }

    public function calculateTotalPricePerSquareMeter(): void
    {
        $buildingArea = $this->getArea();
        $baseTotalPrice = $this->basePricePerSquareMeter * $buildingArea;

        $commonAmenitiesTotalPrice = $this->getCommonAmenitiesTotalPrice();

        $totalPrice = $baseTotalPrice + $commonAmenitiesTotalPrice;
        $this->totalPricePerSquareMeter = $totalPrice / $buildingArea;
    }

    public function setPricePerSquareMeter(float $basePricePerSquareMeter): void
    {
        $this->basePricePerSquareMeter = $basePricePerSquareMeter;
        $this->updateTotalPricePerSquareMeter();
    }

    protected function updateTotalPricePerSquareMeter(): void
    {
        $this->calculateTotalPricePerSquareMeter();
        $this->building->setPricePerSquareMeter($this->totalPricePerSquareMeter);
    }

    public function getPricePerSquareMeter(): float
    {
        return $this->basePricePerSquareMeter;
    }

    public function getTotalPricePerSquareMeter(): float
    {
        return $this->totalPricePerSquareMeter;
    }

    public function getCommonAmenitiesTotalPrice(): float
    {
        return array_reduce($this->commonAmenities, function ($acc, $item) {
            return $acc + $item->getPrice();
        }, 0);
    }

    public function getArea(): float
    {
        /**
         * @var GetAreaInterface
         */
        $building = $this->building;
        return $building->getArea();
    }

    /**
     * @return array<int,LevelInterface>
     */
    public function getLevels()
    {
        return $this->building->getLevels();
    }

    public function getLevelById(int $id): LevelInterface
    {
        return $this->building->getLevelById($id);
    }

    /**
     * @return array<string,FlatInterface>
     */
    public function getFlats(int $mainRoomCount = null)
    {
        return array_reduce(
            $this->building->getLevels(),
            fn($acc, $level) => array_merge($acc, $level->getFlats($mainRoomCount)),
            []
        );
    }

    public function getFlatCount(int $mainRoomCount = null): int
    {
        return count($this->getFlats($mainRoomCount));
    }
}
