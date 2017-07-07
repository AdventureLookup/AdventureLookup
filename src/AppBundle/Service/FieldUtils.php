<?php

namespace AppBundle\Service;

use AppBundle\Entity\TagName;
use Faker;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\UrlType;
use Symfony\Component\Form\FormBuilderInterface;

class FieldUtils
{
    const defaultType = 'string';

    public function getFieldNames(): array
    {
        return ['string', 'text', 'integer', 'boolean', 'url'];
    }

    public function getFieldNameDescriptions(): array
    {
        return [
            'string' => 'string', 'integer' => 'integer', 'boolean' => 'boolean', 'multiline, long text' => 'text', 'url' => 'url'
        ];
    }

    public function getFakerContent(Faker\Generator $faker, string $type): string
    {
        switch ($type) {
            case 'integer':
                return $faker->numberBetween(1, 10);
            case 'boolean':
                return $faker->boolean ? '1' : '0';
            default:
            case 'string':
                return $faker->name;
            case 'url':
                return $faker->url;
            case 'text':
                return $faker->realText(2000);
        }
    }

    public function generateMappingFor(string $type): array
    {
        switch ($type) {
            case 'text':
                return [
                    'type' => 'text',
                ];
                break;
            case 'url':
            case 'string':
                return [
                    'type' => 'text',
                    'fields' => [
                        'keyword' => [
                            'type' => 'keyword',
                            'ignore_above' => 256,
                        ]
                    ]
                ];
                break;
            case 'integer':
                return [
                    'type' => 'integer',
                ];
                break;
            case 'boolean':
                return [
                    'type' => 'boolean',
                ];
                break;
            default:
                throw new \RuntimeException('Invalid type');
        }
    }

    public function getAllowedSearchOperations(string $type): array
    {
        $ops = [];
        if (in_array($type, ['text', 'string', 'url'])) {
            $ops[] = [
                'id' => 'like',
                'desc' => 'contains'
            ];
        }
        if (in_array($type, ['integer', 'string', 'boolean', 'url'])) {
            $ops[] = [
                'id' => 'eq',
                'desc' => 'equals'
            ];
        }
        if ($type == 'integer') {
            $ops[] = [
                'id' => 'gt',
                'desc' => '>'
            ];
            $ops[] = [
                'id' => 'gte',
                'desc' => '>='
            ];
            $ops[] = [
                'id' => 'lt',
                'desc' => '<'
            ];
            $ops[] = [
                'id' => 'lte',
                'desc' => '<'
            ];
        }

        return $ops;
    }

    public function serialize(string $type, string $content)
    {
        switch ($type) {
            case 'boolean':
                return (bool)$content;
            /** @noinspection PhpMissingBreakStatementInspection */
            default:
            case 'string':
            case 'url':
            case 'text':
                return $content;
            case 'integer':
                return (int)$content;
        }
    }

    public function buildEditForm(string $type, FormBuilderInterface $builder)
    {
        switch ($type) {
            default:
            case 'string':
                $builder->add('content', TextType::class, [
                    'required' => true,
                ]);
                return;
            case 'url':
                $builder->add('content', UrlType::class, [
                    'required' => true,
                ]);
                return;
            case 'integer':
                $builder->add('content', IntegerType::class, [
                    'required' => true,
                ]);
                return;
            case 'text':
                $builder->add('content', TextareaType::class, [
                    'required' => true,
                    'attr' => [
                        'rows' => 20
                    ]
                ]);
                return;
        }
    }

    public function getFieldNameForAggregation(TagName $fieldEntity)
    {
        $field = $this->getFieldName($fieldEntity);
        if ($fieldEntity->getType() == 'text') {
            return null;
        }
        if (in_array($fieldEntity->getType(), ['string', 'url'])) {
            $field .= '.keyword';
        }

        return $field;
    }

    public function getTitleField(): TagName
    {
        return (new TagName())
            ->setId('title')
            ->setTitle('Title')
            ->setApproved(true)
            ->setUseAsFilter(false)
            ->setShowInSearchResults(false)
            ->setDescription('The title of the adventure')
            ->setExample('Against the cult of the reptile god')
            ->setType('string');
    }

    /**
     * @param TagName $fieldEntity
     * @return string
     */
    public function getFieldName(TagName $fieldEntity): string
    {
        return $this->getFieldNameById($fieldEntity->getId());
    }

    /**
     * @param string|int $id
     * @return string
     */
    public function getFieldNameById($id): string
    {
        if ($id == 'title') {
            $field = 'title';
        } else {
            $field = 'info_' . $id;
        }
        return $field;
    }

    public function isPartOfQSearch(string $type): bool
    {
        return in_array($type, ['text', 'string'], true);
    }
}