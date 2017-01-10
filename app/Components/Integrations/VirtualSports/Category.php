<?php

namespace App\Components\Integrations\VirtualSports;

use App\Models\Line\Category as CategoryModel;

/**
 * Class Category
 * @package App\Components\Integrations\VirtualSports
 */
class Category
{
    /**
     * @var CategoryModel
     */
    protected $categoryModel;

    /**
     * @param string $categoryName
     * @param $sportId
     * @param $countryId
     * @return bool
     */
    public function create(string $categoryName, int $sportId, int $countryId):bool
    {
        Translate::add($categoryName);

        $category = CategoryModel::findByNameForSport($categoryName, $sportId);
        if ($category === null) {
            $category = new CategoryModel([
                'name' => $categoryName,
                'weigh' => 100,
                'enet_id' => null,
                'sport_id' => $sportId,
                'gender' => 'male',
                'country_id' => $countryId,
                'slug' => null
            ]);
            if (!$category->save()) {
                return false;
            }
        }
        $this->categoryModel = $category;
        return true;
    }

    /**
     * @return int
     * @throws \RuntimeException
     */
    public function getCategoryId():int
    {
        if (!$this->categoryModel) {
            throw new \RuntimeException('Category not defined');
        }
        return (int)$this->categoryModel->id;
    }
}
