<?php
/**
 * Shopware 5
 * Copyright (c) shopware AG
 *
 * According to our dual licensing model, this program can be used either
 * under the terms of the GNU Affero General Public License, version 3,
 * or under a proprietary license.
 *
 * The texts of the GNU Affero General Public License with an additional
 * permission and of our proprietary license can be found at and
 * in the LICENSE file you have received along with this program.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU Affero General Public License for more details.
 *
 * "Shopware" is a registered trademark of shopware AG.
 * The licensing of the program under the AGPLv3 does not imply a
 * trademark license. Therefore any rights, title and interest in
 * our trademarks remain entirely with us.
 */

namespace Shopware\Tests\Functional\Bundle\SearchBundle\Condition;

use Shopware\Bundle\SearchBundle\Condition\ProductAttributeCondition;
use Shopware\Bundle\StoreFrontBundle\Struct\ShopContext;
use Shopware\Models\Category\Category;
use Shopware\Tests\Functional\Bundle\StoreFrontBundle\TestCase;

class ProductAttributeConditionTest extends TestCase
{
    public function testVariants()
    {
        $condition = new ProductAttributeCondition(
            'attr1',
            ProductAttributeCondition::OPERATOR_EQ,
            10
        );

        $product = [
            'mainDetail' => [
                'number' => '5028568_A001_34',
                'attribute' => [
                    'attr1' => 20,
                ],
                'configuratorOptions' => [
                    [
                        'group' => 'Größe',
                        'option' => '34',
                    ],
                ],
                'active' => true,
                'prices' => [
                    [
                        'customerGroupKey' => 'EK',
                        'price' => '899.00',
                        'pseudoPrice' => null,
                    ],
                ],
            ],
            'variants' => [
                [
                    'number' => 'First-Match',
                    'attribute' => [
                        'attr1' => 10,
                    ],
                    'configuratorOptions' => [
                        0 => [
                            'group' => 'Größe',
                            'option' => '36',
                        ],
                    ],
                    'active' => true,
                    'prices' => [
                        0 => [
                            'customerGroupKey' => 'EK',
                            'price' => '899.00',
                            'pseudoPrice' => null,
                        ],
                    ],
                ],
                [
                    'number' => '5028568_A001_38',
                    'attribute' => [
                        'attr1' => 30,
                    ],
                    'configuratorOptions' => [
                        0 => [
                            'group' => 'Größe',
                            'option' => '38',
                        ],
                    ],
                    'active' => true,
                    'prices' => [
                        0 => [
                            'customerGroupKey' => 'EK',
                            'price' => '899.00',
                            'pseudoPrice' => null,
                        ],
                    ],
                ],
            ],
            'configuratorSet' => [
                'groups' => [
                    'Größe' => [
                        'name' => 'Größe',
                        'options' => [
                            [
                                'name' => '34',
                            ],
                            [
                                'name' => '36',
                            ],
                            [
                                'name' => '38',
                            ],
                        ],
                    ],
                ],
            ],
        ];

        $this->search(
            [
                'First-Match' => $product,
                'Not-Match' => ['attr1' => 20],
            ],
            ['First-Match'],
            null,
            [$condition]
        );
    }

    public function testEquals()
    {
        $condition = new ProductAttributeCondition(
            'attr1',
            ProductAttributeCondition::OPERATOR_EQ,
            10
        );

        $this->search(
            [
                'First-Match' => ['attr1' => 10],
                'Not-Match' => ['attr1' => 20],
            ],
            ['First-Match'],
            null,
            [$condition]
        );
    }

    public function testContains()
    {
        $condition = new ProductAttributeCondition(
            'attr1',
            ProductAttributeCondition::OPERATOR_CONTAINS,
            'Rot'
        );

        $this->search(
            [
                'First-Match' => ['attr1' => 'Dunkel-Rot'],
                'Second-Match' => ['attr1' => 'Rot'],
                'Not-Match' => ['attr1' => 'Grün'],
            ],
            ['First-Match', 'Second-Match'],
            null,
            [$condition]
        );
    }

    public function testEndsWith()
    {
        $condition = new ProductAttributeCondition(
            'attr1',
            ProductAttributeCondition::OPERATOR_ENDS_WITH,
            'Grün'
        );

        $this->search(
            [
                'First-Match' => ['attr1' => 'Grün'],
                'Second-Match' => ['attr1' => 'Rot-Grün'],
                'Not-Match' => ['attr1' => 'Grün-Rot'],
                'Not-Match2' => ['attr1' => 'Dunkel-Rot'],
            ],
            ['First-Match', 'Second-Match'],
            null,
            [$condition]
        );
    }

    public function testStartsWith()
    {
        $condition = new ProductAttributeCondition(
            'attr1',
            ProductAttributeCondition::OPERATOR_STARTS_WITH,
            'Grün'
        );

        $this->search(
            [
                'First-Match' => ['attr1' => 'Grün'],
                'Second-Match' => ['attr1' => 'Grün-Rot'],
                'Not-Match' => ['attr1' => 'Rot-Grün'],
                'Not-Match2' => ['attr1' => 'Dunkel-Rot'],
            ],
            ['First-Match', 'Second-Match'],
            null,
            [$condition]
        );
    }

    public function testInOperator()
    {
        $condition = new ProductAttributeCondition(
            'attr1',
            ProductAttributeCondition::OPERATOR_IN,
            ['Grün', 'Rot']
        );

        $this->search(
            [
                'First-Match' => ['attr1' => 'Grün'],
                'Second-Match' => ['attr1' => 'Rot'],
                'Not-Match' => ['attr1' => 'Rot-Grün'],
                'Not-Match2' => ['attr1' => 'Dunkel-Rot'],
            ],
            ['First-Match', 'Second-Match'],
            null,
            [$condition]
        );
    }

    public function testNull()
    {
        $condition = new ProductAttributeCondition(
            'attr1',
            ProductAttributeCondition::OPERATOR_EQ,
            null
        );

        $this->search(
            [
                'First-Match' => ['attr1' => null],
                'Second-Match' => ['attr1' => null],
                'Not-Match' => ['attr1' => 'Rot-Grün'],
                'Not-Match2' => ['attr1' => 'Dunkel-Rot'],
            ],
            ['First-Match', 'Second-Match'],
            null,
            [$condition]
        );
    }

    public function testNotNull()
    {
        $condition = new ProductAttributeCondition(
            'attr1',
            ProductAttributeCondition::OPERATOR_NEQ,
            null
        );

        $this->search(
            [
                'First-Match' => ['attr1' => 'Grün'],
                'Second-Match' => ['attr1' => 'Rot'],
                'Not-Match' => ['attr1' => null],
                'Not-Match2' => ['attr1' => null],
            ],
            ['First-Match', 'Second-Match'],
            null,
            [$condition]
        );
    }

    protected function getProduct(
        $number,
        ShopContext $context,
        Category $category = null,
        $data = ['attr1' => 10]
    ) {
        $product = parent::getProduct($number, $context, $category);
        if (array_key_exists('attr1', $data)) {
            $product['mainDetail']['attribute'] = $data;
        } else {
            $product = array_merge($product, $data);
        }

        return $product;
    }
}
