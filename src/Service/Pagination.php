<?php

namespace App\Service;
use Pagerfanta\Pagerfanta;
use Pagerfanta\Adapter\ArrayAdapter;

class Pagination
{
    public function getPagination($page, $maxPerPage, $items, $nameRessource)
    {
        $adapter = new ArrayAdapter($items);
        $pagerfanta = new Pagerfanta($adapter);
        $pagerfanta
            ->setMaxPerPage($maxPerPage)
            ->setCurrentPage($page);
        
        $returnItems = array();

        foreach($pagerfanta->getCurrentPageResults() as $item){
            $returnItems[] = $item;
        }

        $data = [
            'total' => $pagerfanta->getNbResults(),
            'count' => count($returnItems),
            $nameRessource => $returnItems
        ];

        if ($pagerfanta->hasPreviousPage()){
            $data['previousPage'] = '/api/'.  $nameRessource . '/' . $pagerfanta->getPreviousPage();
        }
        if ($pagerfanta->hasNextPage()){
            $data['nextPage'] = '/api/'.  $nameRessource . '/' . $pagerfanta->getNextPage();
        }

        return $data;
    }
}