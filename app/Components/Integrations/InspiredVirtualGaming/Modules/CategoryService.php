<?php
/**
 * Created by PhpStorm.
 * User: doom_sentinel
 * Date: 1/4/17
 * Time: 2:56 PM
 */

namespace App\Components\Integrations\InspiredVirtualGaming\Modules;


use App\Models\Line\Category;

class CategoryService
{

    protected $name;
    protected $sportId;
    protected $weight;
    protected $gender;
    protected $countryId;

    public function __construct(string $name, int $sportId, int $weight, string $gender, int $countryId)
    {

        $this->name = $name;
        $this->sportId = $sportId;
        $this->weight = $weight;
        $this->gender = $gender;
        $this->countryId = $countryId;
    }

    public function resolve() : Category
    {
        $category = Category::findByNameForSport($this->name, $this->sportId);

        if(!$category) {
            $category = Category::create([
                'name' => $this->name,
                'weigh' => $this->weight,
                'enet_id' => null,
                'sport_id' => $this->sportId,
                'gender' => $this->gender,
                'country_id' => $this->countryId,
                'slug' => null
            ]);
        }

        if(!$category) {
            throw new \RuntimeException("Unable to get a category");
        }

        return $category;
    }
}