<?php

namespace AltDesign\AltSeo\Tags;

use Statamic\Facades\Antlers;
use Statamic\Tags\Tags;

use AltDesign\AltSeo\Helpers\Data;
use Statamic\Assets\AssetRepository;

/**
 * Class AltSeo
 *
 * @package  AltDesign\AltSeo
 * @author   Ben Harvey <ben@alt-design.net>, Natalie Higgins <natalie@alt-design.net>
 * @license  Copyright (C) Alt Design Limited - All Rights Reserved - licensed under the MIT license
 * @link     https://alt-design.net
 */
class AltSeo extends Tags
{
    /**
     * The {{ alt_seo }} tag.
     *
     * @return string|array
     */
    public function index()
    {
        // Default doesn't actually do anything here
        return;
    }

    /**
     * The {{ alt_seo:title }} tag.
     *
     * @return string
     */
    public function title()
    {
        return '<title>' . $this->getTitle() . '</title>';
    }

    /**
     * The {{ alt_seo:meta }} tag.
     *
     * @return string|array
     */
    public function meta()
    {
        $returnString = '<title>' . $this->getTitle() . '</title>';
        $returnString .= '<meta name="description" content="' . strip_tags($this->getDescription()) . '" />';
        $returnString .= '<!-- Facebook Meta Tags -->';
        $returnString .= '<meta property="og:url" content="' . $this->getCurrentUrl() . '">';
        $returnString .= '<meta property="og:type" content="website">';
        $returnString .= '<meta property="og:title" content="' . $this->getSocialTitle() . '">';
        $returnString .= '<meta property="og:description" content="' . strip_tags($this->getSocialDescription()) . '">';
        $returnString .= '<meta property="og:image" content="' . $this->getSocialImage() . '">';
        $returnString .= '<!-- Twitter Meta Tags -->';
        $returnString .= '<meta name="twitter:card" content="summary_large_image">';
        $returnString .= '<meta property="twitter:domain" content="' . ENV('APP_URL') . '">';
        $returnString .= '<meta property="twitter:url" content="' . $this->getCurrentUrl() . '">';
        $returnString .= '<meta name="twitter:title" content="' . $this->getSocialTitle() . '">';
        $returnString .= '<meta name="twitter:description" content="' . strip_tags($this->getSocialDescription()) . '">';
        $returnString .= '<meta property="twitter:image" content="' . $this->getSocialImage() . '">';

        return $returnString;
    }

    /**
     * Replace the variables in the string.
     *
     * @param $string
     * @return array|string|string[]
     */
    public function replaceVars($string)
    {
        $blueprintPageTitle = $this->context->value('title'); // Page Title
        $appName = $this->context->value('config.app.name'); // App Name
        $string = str_replace('{title}', $blueprintPageTitle, $string);
        $string = str_replace('{site_name}', $appName, $string);
        return $string;
    }

    /**
     * Bring the title in and return the correct instance.
     *
     * @return array|string|string[]
     */
    public function getTitle()
    {
        if (!empty($this->context->value('alt_seo_meta_title'))) {
            return $this->replaceVars($this->context->value('alt_seo_meta_title'));
        }

        $data = new Data('settings');
        if ($data->get('alt_seo_meta_title_default')) {
            $title = $data->get('alt_seo_meta_title_default');
            return $this->replaceVars($title);
        }

        return $this->context->value('title') . ' | ' . $this->context->value('config.app.name');
    }

    /**
     * Bring the description in and return the correct instance.
     *
     * @return mixed|string
     */
    public function getDescription()
    {
        if (!empty($this->context->value('alt_seo_meta_description'))) {
            return Antlers::parse($this->replaceVars($this->context->value('alt_seo_meta_description')));
        }

        $data = new Data('settings');
        if ($data->get('alt_seo_meta_description_default')) {
            $description = $data->get('alt_seo_meta_description_default');
            $description = $this->replaceVars($description);
            return Antlers::parse($description);
        }

        return '';
    }

    /**
     * Bring the social title in and return the correct instance.
     *
     * @return array|string|string[]
     */
    public function getSocialTitle()
    {
        if (!empty($this->context->value('alt_seo_social_title'))) {
            return $this->replaceVars($this->context->value('alt_seo_social_title'));
        }

        $data = new Data('settings');
        if ($data->get('alt_seo_social_title_default')) {
            $title = $data->get('alt_seo_social_title_default');
            return $this->replaceVars($title);
        }

        return $this->context->value('title') . ' | ' . $this->context->value('config.app.name');
    }

    /**
     * Bring the social description in and return the correct instance.
     *
     * @return array|mixed|string|string[]
     */
    public function getSocialDescription()
    {

        $socialDescription = '';

        if (!empty($this->context->value('alt_seo_social_description'))) {
            $socialDescription = Antlers::parse($this->replaceVars($this->context->value('alt_seo_social_description')));
        } else {
            $data = new Data('settings');
            if ($data->get('alt_seo_social_description_default')) {
                $description = $data->get('alt_seo_social_description_default');
                $description = $this->replaceVars($description);
                $description = Antlers::parse($description);

                $socialDescription = $description;
            }
        }

        if (str_contains($socialDescription, '{description}')) {
            $description = $this->getDescription();
            $socialDescription = str_replace('{description}', $description, $socialDescription);
        }

        return $socialDescription;
    }


    /**
     * Bring the social image in and return the correct instance.
     *
     * @return array|mixed|string|string[]|null
     */
    public function getSocialImage()
    {
        $imageURL = '';
        if (!empty($this->context->value('alt_seo_social_image'))) {
            $imageURL = str_replace('/assets/', '', Antlers::parse($this->context->value('alt_seo_social_image')));
        } else {
            $data = new Data('settings');

            if ($data->get('alt_seo_social_image_default')) {
                $path = $data->get('alt_seo_social_image_default');
                $image = (new AssetRepository)->all()->filter(function ($asset) use ($path) {
                    return $asset->path() === $path;
                })->first();
                $imageURL = $image ? Antlers::parse($image) : null;
            }
        }

        return $imageURL;
    }

    public function getCurrentUrl()
    {
        return $this->context->get('current_full_url');
    }
}
