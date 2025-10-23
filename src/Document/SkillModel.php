<?php

namespace App\Document;

class SkillModel extends BaseJSONModel
{
    protected string $_templatePrefix = 'skill_';

    public function __construct(array $json, string $lang = 'de')
    {
        $current_year = intval(date('Y'));
        $json['years'] = ($current_year - $json['since']) . ' ' . ($lang == 'de' ? 'Jahre' : 'years');
        unset($json['since']);
        parent::__construct($json, $lang);
    }

    /**
     * Groups skills by category and subcategory
     *
     * @param ApplicantModel $applicant - the applicant whose skills to group ()
     * @return array - grouped skills by category and subcategory in the format:
     * [
     *   [
     *     'category' => 'Category Name',
     *    'subCategory' => 'Subcategory Name', // optional
     *    'skills' => [SkillModel, SkillModel, ...]
     *  ],
     */
    public static function groupByCategories(ApplicantModel $applicant): array
    {
        $categories = array_unique(array_map(fn ($item) => $item->category, $applicant->skills));
        $skillsByCategory = array();
        $skillsBySubCategory = array();
        foreach ($categories as $category) {
            $skillsByCategory[] = array(
                'category' => $category,
                'skills' => array_filter($applicant->skills, fn ($item) => $item->category === $category)
            );
        }
        foreach ($skillsByCategory as $skillsByCategoryItem) {
            $subCategories = array_unique(array_map(fn ($item) => $item->subCategory, $skillsByCategoryItem['skills']));
            $category = $skillsByCategoryItem['category'];
            foreach ($subCategories as $subCategory) {
                if ($subCategory) {
                    $skillsBySubCategory[] = array(
                        'category' => $category,
                        'subCategory' => $subCategory,
                        'skills' => array_filter($skillsByCategoryItem['skills'], fn ($item) => $item->subCategory === $subCategory)
                    );
                } else {
                    $skillsBySubCategory[] = $skillsByCategoryItem;
                }
            }
        }
        return $skillsBySubCategory;
    }
}
