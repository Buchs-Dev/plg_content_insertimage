<?php

/**
 * @copyright   (C) 2023 Buchs A/S <https://buchs.dk/>
 * @license     GNU General Public License version 2 or later; see LICENSE.txt
 */

use Joomla\CMS\HTML\HTMLHelper;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\Uri\Uri;

\defined('_JEXEC') or die;

class PlgContentInsertimage extends CMSPlugin
{
    public function onContentPrepare($context, &$article, &$params, $page = 0)
    {
        // Don't run this plugin when the content is being indexed
        if ($context === 'com_finder.indexer') {
            return;
        }

        // Only execute if $article is an object and has a text property
        if (!is_object($article) || !property_exists($article, 'text') || is_null($article->text)) {
            return;
        }

        // Expression to search for inserted images
        $regex = '/{insertimage\s(.*?)}/i';

        if (str_contains($article->text, '{insertimage ')) {
            // Find all instances of plugin and put in $matches for insertimage
            // $matches[0] is full pattern match, $matches[1] are the args
            preg_match_all($regex, $article->text, $matches, PREG_SET_ORDER);

            // No matches, skip this
            if ($matches) {
                foreach ($matches as $match) {

                    // $output = '<pre>' . print_r($this->_shortcodeParseAtts($match[1]), true) . '</pre>';
                    $imageAttributes = $this->_shortcodeParseAtts($match[1]);
                    $output = $this->_buildImage($imageAttributes);

                    // We should replace only first occurrence in order to allow positions with the same name to regenerate their content:
                    if (($start = strpos($article->text, $match[0])) !== false) {
                        $article->text = substr_replace($article->text, $output, $start, strlen($match[0]));
                    }
                }
            }
        }
    }

    protected function _buildImage($image) {

        $base = Uri::base(true);

        $imageProcessor = $this->params->get('image_processor', 'ir');

        $bsWidth = $this->_getBootstrapContainerWidths();
        $bsGutter = $this->_getBootstrapGutter();

        // Set default image container widths
        $image['xs'] = $image['xs'] ?? 12;
        $image['sm'] = $image['sm'] ?? $image['xs']; //      \
        $image['md'] = $image['md'] ?? $image['sm']; //       \
        $image['lg'] = $image['lg'] ?? $image['md']; //         Inherit if not explicitly set
        $image['xl'] = $image['xl'] ?? $image['lg']; //       /  
        $image['xxl'] = $image['xxl'] ?? $image['xl']; //    /

        // Calculate sizes
        $locale = setlocale(LC_NUMERIC, 0);
        setlocale(LC_NUMERIC, 'en_US');

        $image_width['xs'] = 'calc((100vw - ' . 12 / $image['xs'] * $bsGutter . 'px) / ' . 12 / $image['xs'] . ')';
        $image_width['sm'] = $bsWidth['sm'] / 12 * $image['sm'] - $bsGutter . 'px';
        $image_width['md'] = $bsWidth['md'] / 12 * $image['md'] - $bsGutter . 'px';
        $image_width['lg'] = $bsWidth['lg'] / 12 * $image['lg'] - $bsGutter . 'px';
        $image_width['xl'] = $bsWidth['xl'] / 12 * $image['xl'] - $bsGutter . 'px';
        $image_width['xxl'] = $bsWidth['xxl'] / 12 * $image['xxl'] - $bsGutter . 'px';

        if (($image['container'] ?? false) == 'fluid') {
            $image_width['xs'] = 100 / 12 * $image['xs'] . 'vw';
            $image_width['sm'] = 100 / 12 * $image['sm'] . 'vw';
            $image_width['md'] = 100 / 12 * $image['md'] . 'vw';
            $image_width['lg'] = 100 / 12 * $image['lg'] . 'vw';
            $image_width['xl'] = 100 / 12 * $image['xl'] . 'vw';
            $image_width['xxl'] = 100 / 12 * $image['xxl'] . 'vw';
        }

        setlocale(LC_NUMERIC, $locale);

        // Classes
        $classes = $image['class'] ?? 'img-full';
        $figureClasses = $image['figure-class'] ?? null;

        // Crop to aspect ratio
        $ar = ($image['ar'] ?? false) ? ('-c' . $image['ar']) : null;

        // Title
        $title = $image['title'] ?? null;

        // Alt text
        $alt = $image['alt'] ?? null;

        // Caption
        $caption = ($image['caption'] ?? false) ? '<figcaption>' . $image['caption'] . '</figcaption>' : null;

        // Clean the image URL
        $src = HTMLHelper::cleanImageURL($image['src'])->url;

        // Analyze the filename
        $extension = strtolower(pathinfo($image['src'])['extension']);

        // Wrap in figure element?
        $figure = ($image['figure'] ?? true) !== 'off';

        $output = [];
        $outputReverse = [];

        if ($figure) {
            $output[] = "<figure class=\"$figureClasses\">";
            $outputReverse[] = '</figure>';
            $outputReverse[] = $caption;
        }
        
        // Process jpeg and png images
        if ($imageProcessor && ($extension == 'jpg' || $extension == 'jpeg' || $extension == 'png')) {
            $output[] = '<picture class="img-full">';

            // Get sizes attribute
            $sizesAttr = $this->_buildSizesAttr($image_width);

            // Add a webp version of the image
            if ($imageProcessor == 'ir' && $this->params->get('webp', 1)) {
                $output[] = "<source type=\"image/webp\" srcset=\"{$this->_buildSrcset($src, $ar, true)}\" sizes=\"$sizesAttr\">";
            }

            // Add normal img tag
            $output[] = "<img title=\"$title\" class=\"$classes\" src=\"$base/{$this->params->get('slir_path', 'slir')}/w1024$ar/$src\" srcset=\"{$this->_buildSrcset($src, $ar)}\" alt=\"$alt\" sizes=\"$sizesAttr\" loading=\"lazy\">";

            // Close picture tag
            $output[] = '</picture>';

        // Generate img tag for image types that can't be processed
        } elseif ($extension == 'gif' || $extension == 'svg' || !$imageProcessor) {
            $output[] = "<img title=\"$title\" class=\"$classes\" src=\"$base/$src\" alt=\"$alt\" loading=\"lazy\">";

        // Unsupported filetype
        } else {
            $output = ['<pre>' . Text::_('PLG_INSERTIMAGE_TEXT_UNSUPPORTED_FILETYPE') . '</pre>'];
            $outputReverse = [''];
        }

        $output = implode($output) . implode(array_reverse($outputReverse));

        return $output;
    }

    protected function _buildSrcset($src, $ar, $webp = false) {
        $base = Uri::base(true);
        $widths = $this->params->get('image_widths', '2560, 2048, 1536, 1280, 1024, 768, 640, 480, 360, 320');
        $widths = array_map('intval', explode(',', $widths));
        rsort($widths);

        $srcset = [];
        foreach ($widths as $w) {
            if ($w) {
                $srcset[] = $base . '/' . $this->params->get('slir_path', 'slir') . '/w' . $w . $ar . '/' . $src . ($webp ? '.webp' : '') . ' ' . $w . 'w';
            }
        }

        return implode(', ', $srcset);
    }

    protected function _buildSizesAttr($image_width) {
        $bootstrapVersion = $this->params->get('bootstrap_version', 3);
        
        if ($bootstrapVersion == 3) {
            $s =  '(min-width: 1200px) ' . $image_width['lg'] .
                ', (min-width: 992px) '  . $image_width['md'] .
                ', (min-width: 768px) '  . $image_width['sm'] .
                ', ' . $image_width['xs'];
        }

        if ($bootstrapVersion == 4) {
            $s =  '(min-width: 1200px) ' . $image_width['xl'] .
                ', (min-width: 992px) '  . $image_width['lg'] .
                ', (min-width: 768px) '  . $image_width['md'] .
                ', (min-width: 576px) '  . $image_width['sm'] .
                ', ' . $image_width['xs'];
        }

        if ($bootstrapVersion == 5) {
            $s =  '(min-width: 1400px) ' . $image_width['xxl'] .
                ', (min-width: 1200px) ' . $image_width['xl'] .
                ', (min-width: 992px) '  . $image_width['lg'] .
                ', (min-width: 768px) '  . $image_width['md'] .
                ', (min-width: 576px) '  . $image_width['sm'] .
                ', ' . $image_width['xs'];
        }

        return $s;
    }

    protected function _getBootstrapContainerWidths() {
        $bootstrapVersion = $this->params->get('bootstrap_version', 3);

        $bsWidths = [
            3 => [ 'sm' => 750, 'md' => 970, 'lg' => 1170, 'xl' => 1170, 'xxl' => 1170 ],
            4 => [ 'sm' => 540, 'md' => 720, 'lg' => 960, 'xl' => 1140, 'xxl' => 1140 ],
            5 => [ 'sm' => 540, 'md' => 720, 'lg' => 960, 'xl' => 1140, 'xxl' => 1320 ]
        ];

        return $bsWidths[$bootstrapVersion];
    }

    protected function _getBootstrapGutter() {
        $bootstrapVersion = $this->params->get('bootstrap_version', 3);

        $bsGutter = [
            3 => 30,
            4 => 30,
            5 => 24
        ];

        return $bsGutter[$bootstrapVersion];
    }

    protected function _getShortcodeAttsRegex() {
        return '/([\w-]+)\s*=\s*"([^"]*)"(?:\s|$)|([\w-]+)\s*=\s*\'([^\']*)\'(?:\s|$)|([\w-]+)\s*=\s*([^\s\'"]+)(?:\s|$)|"([^"]*)"(?:\s|$)|\'([^\']*)\'(?:\s|$)|(\S+)(?:\s|$)/';
    }

    protected function _shortcodeParseAtts( $text ) {
        $atts    = array();
        $pattern = $this->_getShortcodeAttsRegex();
        $text    = preg_replace( "/[\x{00a0}\x{200b}]+/u", ' ', $text );

        if ( preg_match_all( $pattern, $text, $match, PREG_SET_ORDER ) ) {
            foreach ( $match as $m ) {
                if ( ! empty( $m[1] ) ) {
                    $atts[ strtolower( $m[1] ) ] = stripcslashes( $m[2] );
                } elseif ( ! empty( $m[3] ) ) {
                    $atts[ strtolower( $m[3] ) ] = stripcslashes( $m[4] );
                } elseif ( ! empty( $m[5] ) ) {
                    $atts[ strtolower( $m[5] ) ] = stripcslashes( $m[6] );
                } elseif ( isset( $m[7] ) && strlen( $m[7] ) ) {
                    $atts[] = stripcslashes( $m[7] );
                } elseif ( isset( $m[8] ) && strlen( $m[8] ) ) {
                    $atts[] = stripcslashes( $m[8] );
                } elseif ( isset( $m[9] ) ) {
                    $atts[] = stripcslashes( $m[9] );
                }
            }

            // Reject any unclosed HTML elements.
            foreach ( $atts as &$value ) {
                if ( false !== strpos( $value, '<' ) ) {
                    if ( 1 !== preg_match( '/^[^<]*+(?:<[^>]*+>[^<]*+)*+$/', $value ) ) {
                        $value = '';
                    }
                }
            }
        } else {
            $atts = ltrim( $text );
        }

        return $atts;
    }
}
