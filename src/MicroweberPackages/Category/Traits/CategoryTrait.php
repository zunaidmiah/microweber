<?php

namespace MicroweberPackages\Category\Traits;

use MicroweberPackages\Category\Models\Category;
use MicroweberPackages\Category\Models\CategoryItem;

trait CategoryTrait
{

    private $_addContentToCategory = null;
    private $_removeFromAllCategories = false;

    public function initializeCategoryTrait()
    {
      //  $this->appends[] = 'categories';
        //	$this->with[] = 'categoryItems';
        $this->fillable[] = 'category_ids';
    }


    public function scopeWhereCategoryIds($query, $ids = false) {

       // $excludeIds = [];
        $table = $this->getMorphClass();

        if (is_string($ids)) {
            $ids = explode(',', $ids);
        } elseif (is_int($ids)) {
            $ids = array($ids);
        }

        if (is_array($ids)) {
            $ids = array_filter($ids);
            if (!empty($ids)) {
             //   if (!isset($search_joined_tables_check['categories_items'])) {
                     $query->whereIn('content.id', function ($subQuery) use ($table, $ids) {
                        $subQuery->select('categories_items.rel_id');
                        $subQuery->from('categories_items');
                        $subQuery->where('categories_items.rel_type', '=', $table);
                        $subQuery->whereIn('categories_items.parent_id',$ids);
                        $subQuery->groupBy('categories_items.rel_id');
                        return $subQuery;
                    });


//                    $query = $query->join('categories_items', function ($join) use ($table, $ids) {
//                        $join->on('categories_items.rel_id', '=', $table . '.id')->where('categories_items.rel_type', '=', $table);
//                        /*if ($excludeIds) {
//                            $join->whereNotIn('categories_items.rel_id', $excludeIds);
//                        }*/
//                        $join->whereIn('categories_items.parent_id', $ids)->distinct();
//                    });
             //   }
                //$query = $query->distinct();

            }
        }

        return $query;
    }

    /* public function addToCategory($contentId)
     {
         $this->_addContentToCategory[] = $contentId;
     }*/

    public static function bootCategoryTrait()
    {
        static::saving(function ($model) {
            // append content to categories
            if (isset($model->category_ids)) {
                $model->_addContentToCategory = $model->category_ids;
            }
            unset($model->category_ids);
        });

        static::saved(function ($model) {
            if (isset($model->_addContentToCategory)) {
                $model->_setCategories($model->_addContentToCategory);
            }
        });
    }

    private function _setCategories($categoryIds)
    {

        if (!is_array($categoryIds)) {
            $categoryIds = explode(',', $categoryIds);
        }

        //delete from categories if 1st category is 0
        if (reset($categoryIds) == 0) {
            CategoryItem::where('rel_id', $this->id)->where('rel_type', $this->getMorphClass())->delete();
            return;
        }

        $entityCategories = CategoryItem::where('rel_id', $this->id)->where('rel_type', $this->getMorphClass())->get();
        if ($entityCategories) {
            foreach ($entityCategories as $entityCategory) {
                if (!in_array($entityCategory->parent_id, $categoryIds)) {
                    $entityCategory->delete();
                }
            }
        }

        if (!empty($categoryIds)) {
            foreach ($categoryIds as $categoryId) {

                $categoryItem = CategoryItem::where('rel_id', $this->id)->where('rel_type', $this->getMorphClass())->where('parent_id', $categoryId)->first();
                if (!$categoryItem) {
                    $categoryItem = new CategoryItem();
                }

                $categoryItem->rel_id = $this->id;
                $categoryItem->rel_type = $this->getMorphClass();
                $categoryItem->parent_id = $categoryId;
                $categoryItem->save();
            }
        }

    }
/*
    public function categories()
    {
        return $this->hasMany(Category::class, 'rel_id');
    }*/

    public function categoryItems()
    {
        return $this->hasMany(CategoryItem::class, 'rel_id');
    }

    public function getParentsByCategoryId($id)
    {
        $findCategory = Category::select(['id','parent_id'])->where('id', $id)->first();
        if ($findCategory) {
            $category = $findCategory->toArray();
            $category['parents'] = $this->getParentsByCategoryId($findCategory->parent_id);
            return $category;
        }

        return [];
    }

    public function getCategoriesAttribute()
    {
        $categories = [];
        foreach ($this->categoryItems()->with('category')->get() as $category) {
            $categories[] = $category;
        }
        return collect($categories);
    }

    public function hasCategories()
    {
        $categories = $this->categoryNames();
        if ($categories) {
            return true;
        }
        return false;
    }

    public function categoryName()
    {
        $categories = $this->getCategoriesAttribute();
        if ($categories) {
            $categoryTitle = category_title($categories[0]->id);
            if (!empty($categoryTitle)) {
                return $categoryTitle;
            }
        }
        return false;
    }

    public function categoryNames()
    {
        $categories = $this->getCategoriesAttribute();
        if ($categories) {
            $categoryNames = [];
            foreach ($categories as $category) {
                $categoryTitle = category_title($category->id);
                if (empty($categoryTitle)) {
                    continue;
                }
                $categoryNames[] = $categoryTitle;
            }
            return implode(', ', $categoryNames);
        }
        return false;
    }
}
