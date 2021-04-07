<?php

namespace modules\TwigExtensions\extensions;

use Craft;
use Twig\Extension\AbstractExtension;
use modules\TwigExtensions\traits\TwigExtensionsTrait;

class ChunkByPropertyExtension extends AbstractExtension
{
    use TwigExtensionsTrait;

    /**
     * @return string
     */
    public function getName()
    {
        return Craft::t('twigextensions', 'Chunk Array By Property Extension');
    }

    /**
     * @return array|\Twig_Filter[]
     */
    public function getFunctions()
    {
        return [
            $this->addFunction('chunkByProperty'),
        ];
    }

    /**
     * @param string $string
     * @return string
     * Splits an array between a value in that array, and returns 3 parts
     */
    public function chunkByProperty($nodes, array $property):array
    {
        $key = $property['property'];
        $value = $property['value'];

        $split = array_search($value, array_column($nodes, $key));

        $firstPart = array_slice($nodes, 0, $split);   // first part
        $secondPart = array_slice($nodes, $split + 1); // second part

        return [
            'first' => $firstPart,
            //'split' => $split,
            'last' => $secondPart,
        ];
    }
}
