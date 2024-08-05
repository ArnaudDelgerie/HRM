<?php

namespace App\Trait;

use Doctrine\ORM\Query;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;

trait PaginatorTrait
{
    /**
     * @return Paginator
     */
    public static function paginate(Query $query, int $page, int $limit): Paginator
    {
        if($page < 1) $page = 1;
        
        $firstResult = ($page - 1) * $limit;

        $query->setMaxResults($limit)->setFirstResult($firstResult);

        $paginator = new Paginator($query, true);

        return $paginator;
    }
    /**
     * @param Paginator $paginator
     * @return array<int,array<string,mixed>>
     */
    public static function getPaginationData(Paginator $paginator, Request $request, int $page, int $limit): array
    {
        $paginationData = [];
        $nbResult = $paginator->count();
        $nbPage = round(ceil($nbResult / $limit));
        $routeName = $request->get('_route');
        $queryParameters = $request->query->all();
        $routeParameters = $request->attributes->get('_route_params');
        for($i = 1; $i <= $nbPage; $i++) {
            $queryParameters['page'] = $i;
            $paginationData[] = [
                'page' => $i,
                'current' => $page === $i,
                'routeName' => $routeName,
                'queryParameters' => array_merge($routeParameters, $queryParameters), 
            ]; 
        }

        return $paginationData;
    }
}
