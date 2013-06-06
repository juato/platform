<?php

namespace Oro\Bundle\FormBundle\Tests\Unit\EntityAutocomplete\Doctrine;

use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Persistence\ManagerRegistry;

use Oro\Bundle\FormBundle\EntityAutocomplete\Property;
use Oro\Bundle\FormBundle\EntityAutocomplete\Doctrine\EntitySearchFactory;
use Oro\Bundle\FormBundle\EntityAutocomplete\Doctrine\EntitySearchHandler;

use Oro\Bundle\FormBundle\Tests\Unit\MockHelper;

class EntitySearchFactoryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ManagerRegistry|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $managerRegistry;

    /**
     * @var EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityManager;

    /**
     * @var EntityRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $entityRepository;

    /**
     * @var QueryBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    protected $queryBuilder;

    /**
     * @var EntitySearchFactory
     */
    protected $factory;

    protected function setUp()
    {
        $this->managerRegistry = $this->getMock('Doctrine\Common\Persistence\ManagerRegistry');

        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getRepository'))
            ->getMock();

        $this->entityManager = $this->getMockBuilder('Doctrine\ORM\EntityManager')
            ->disableOriginalConstructor()
            ->setMethods(array('getRepository'))
            ->getMock();

        $this->entityRepository = $this->getMockBuilder('Doctrine\ORM\EntityRepository')
            ->disableOriginalConstructor()
            ->setMethods(array('createQueryBuilder'))
            ->getMock();

        $this->queryBuilder = $this->getMockBuilder('Doctrine\ORM\QueryBuilder')
            ->disableOriginalConstructor()
            ->getMock();

        $this->factory = new EntitySearchFactory($this->managerRegistry);
    }

    /**
     * @dataProvider createDataProvider
     * @param array $options
     * @param array $expectManagerRegistryCalls
     * @param array $expectEntityManagerCalls
     * @param array $expectEntityRepositoryCalls
     * @param array $expectQueryBuilderCalls
     */
    public function testCreate(
        array $options,
        array $expectManagerRegistryCalls,
        array $expectEntityManagerCalls,
        array $expectEntityRepositoryCalls,
        array $expectQueryBuilderCalls
    ) {
        MockHelper::addMockExpectedCalls($this->managerRegistry, $expectManagerRegistryCalls, $this);
        MockHelper::addMockExpectedCalls($this->entityManager, $expectEntityManagerCalls, $this);
        MockHelper::addMockExpectedCalls($this->entityRepository, $expectEntityRepositoryCalls, $this);
        MockHelper::addMockExpectedCalls($this->queryBuilder, $expectQueryBuilderCalls, $this);

        $searchHandler = $this->factory->create($options);

        $this->assertInstanceOf(
            'Oro\Bundle\FormBundle\EntityAutocomplete\Doctrine\EntitySearchHandler',
            $searchHandler
        );

        $this->assertAttributeInstanceOf(
            'Oro\Bundle\FormBundle\EntityAutocomplete\Doctrine\QueryBuilderSearchHandler',
            'queryBuilderSearchHandler',
            $searchHandler
        );
    }

    /**
     * @return array
     */
    public function createDataProvider()
    {
        return array(
            'without entity_manager option' => array(
                'options' => array(
                    'properties' => array($this->createProperty('foo')),
                    'entity_class' => 'FooClassName'
                ),
                'expectManagerRegistryCalls' => array(
                    array('getManager', array(null), 'getMockEntityManager'),
                ),
                'expectEntityManagerCalls' => array(
                    array('getRepository', array('FooClassName'), 'getMockEntityRepository'),
                ),
                'expectRepositoryCalls' => array(
                    array('createQueryBuilder', array('e'), 'getMockQueryBuilder'),
                ),
                'expectQueryBuilderCalls' => array()
            ),
            'with entity_manager option' => array(
                'options' => array(
                    'properties' => array($this->createProperty('foo')),
                    'options' => array(
                        'entity_manager' => 'foo_entity_manager'
                    ),
                    'entity_class' => 'FooClassName'
                ),
                'expectManagerRegistryCalls' => array(
                    array('getManager', array('foo_entity_manager'), 'getMockEntityManager'),
                ),
                'expectEntityManagerCalls' => array(
                    array('getRepository', array('FooClassName'), 'getMockEntityRepository'),
                ),
                'expectRepositoryCalls' => array(
                    array('createQueryBuilder', array('e'), 'getMockQueryBuilder'),
                ),
                'expectQueryBuilderCalls' => array()
            )
        );
    }

    /**
     * @dataProvider createFailsDataProvider
     * @param array $options
     * @param string $expectedException
     * @param string $expectedExceptionMessage
     */
    public function testCreateFails(
        array $options,
        $expectedException,
        $expectedExceptionMessage
    ) {
        $this->setExpectedException($expectedException, $expectedExceptionMessage);
        $this->factory->create($options);
    }

    /**
     * @return array
     */
    public function createFailsDataProvider()
    {
        return array(
            'properties required' => array(
                'options' => array(),
                'RuntimeException',
                'Option "properties" is required'
            ),
            'entity_class required' => array(
                'options' => array(
                    'properties' => array($this->createProperty('foo'))
                ),
                'RuntimeException',
                'Option "entity_class" is required'
            )
        );
    }

    /**
     * @return EntityManager|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockEntityManager()
    {
        return $this->entityManager;
    }

    /**
     * @return EntityRepository|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockEntityRepository()
    {
        return $this->entityRepository;
    }

    /**
     * @return QueryBuilder|\PHPUnit_Framework_MockObject_MockObject
     */
    public function getMockQueryBuilder()
    {
        return $this->queryBuilder;
    }

    /**
     * @param string $name
     * @return Property
     */
    protected function createProperty($name)
    {
        return new Property(array('name' => $name));
    }
}