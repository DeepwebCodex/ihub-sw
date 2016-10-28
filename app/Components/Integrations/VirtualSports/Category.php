<?php

namespace App\Components\Integrations\VirtualSports;

use App\Exceptions\Api\ApiHttpException;
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
     * @return void
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    public function create(string $categoryName, $sportId, $countryId):void
    {
        Translate::add($categoryName);

        $category = CategoryModel::findByNameForSport($categoryName, $sportId);
        if ($category === false) {
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
                throw new ApiHttpException(400, 'error_create_category');
            }
            $this->categoryModel = $category;
        }
    }

    /**
     * @return int
     * @throws \App\Exceptions\Api\ApiHttpException
     */
    public function getCategoryId():int
    {
        if (!$this->categoryModel) {
            throw new ApiHttpException(400, 'error_create_category');
        }
        return (int)$this->categoryModel->id;
    }
}
