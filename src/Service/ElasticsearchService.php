<?php

namespace App\Service;

use Doctrine\ORM\EntityManagerInterface;
use App\Repository\TaskRepository;
use App\Entity\Task;
use App\Repository\UserRepository;
use App\Entity\User;
use FOS\ElasticaBundle\Finder\TransformedFinder;
use Elastica\Util;

/**
 * Class ElasticsearchService
 * @package App\Service
 */
class ElasticsearchService
{
    /** @var  TaskRepository */
    protected $taskRepository;

    /** @var  UserRepository */
    protected $userRepository;

    /** @var TransformedFinder */
    private $transformedFinder;

    /**
     * ElasticsearchService constructor.
     * @param EntityManagerInterface $em
     * @param TransformedFinder $transformedFinder
     */
    public function __construct(EntityManagerInterface $em, TransformedFinder $transformedFinder)
    {
        $this->taskRepository = $em->getRepository(Task::class);
        $this->userRepository = $em->getRepository(User::class);
        $this->transformedFinder = $transformedFinder;
    }

    /**
     * @param array $query
     * @return array
     */
    public function getList(array $query)
    {
        $query = $this->buildQuery($query);
        $results = $this->transformedFinder->find($query);

        return $results;
    }

    /**
     * @param array $query
     * @return \Elastica\Query\BoolQuery
     */
    public function buildQuery(array $query)
    {
        $filterUser = $query['user'] ?? '';
        $filterDateStart = $query['date_start'] ?? '';
        $filterDateEnd = $query['date_end'] ?? '';
        $filterDate = [];
        $result = new \Elastica\Query\BoolQuery();

        if ($filterUser) {
            $textQuery = new \Elastica\Query\MultiMatch();
            $textQuery->setQuery($filterUser);
            $textQuery->setFields(['user']);
            $result->addMust($textQuery);
        }

        if ($filterDateStart) {
            $filterDate['gte'] = Util::convertDate(strtotime($filterDateStart));
        }

        if ($filterDateEnd) {
            $filterDate['lte'] = Util::convertDate(strtotime($filterDateEnd));
        }

        if ($filterDate) {
            $result->addMust(
                new \Elastica\Query\Range(
                    'created_at', $filterDate
                )
            );
        }

        return $result;
    }
}
