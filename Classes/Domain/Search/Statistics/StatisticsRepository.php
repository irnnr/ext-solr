<?php

namespace ApacheSolrForTypo3\Solr\Domain\Search\Statistics;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2016 Thomas Hohn <tho@systime.dk>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/


/**
 * Calculates the SearchQueryStatistics
 *
 * @author Thomas Hohn <tho@systime.dk>
 * @package TYPO3
 * @subpackage solr
 */
class StatisticsRepository
{
    /**
     * Fetches must popular search keys words from the table tx_solr_statistics
     *
     * @param int $rootPageId
     * @param int $limit
     *
     * @return mixed
     */
    public function getSearchStatistics($rootPageId, $limit = 10)
    {
        $rootPageId = (int) $rootPageId;
        $limit = (int) $limit;
        $statisticsRows = $GLOBALS['TYPO3_DB']->exec_SELECTgetRows(
            'keywords, count(keywords) as count, num_found as hits',
            'tx_solr_statistics',
            'root_pid = ' . $rootPageId,
            'keywords',
            'count DESC, hits DESC, keywords ASC',
            $limit
        );

        $numRows = count($statisticsRows);
        $statisticsRows = array_map(function($row) use ($numRows) {
            $row['percent'] = $row['count'] * 100 / $numRows;

            return $row;
        }, $statisticsRows);

        return $statisticsRows;
    }

    /**
     * Find Top search keywords with results
     *
     * @param int $rootPageId
     * @param int $limit
     *
     * @return string
     */
    public function getTopKeyWordsWithHits($rootPageId, $limit = 10)
    {
        return $this->getTopKeyWordsWithOrWithoutHits($rootPageId, $limit, false);
    }

    /**
     * Find Top search keywords without results
     *
     * @param int $rootPageId
     * @param int $limit
     *
     * @return string
     */
    public function getTopKeyWordsWithoutHits($rootPageId, $limit = 10)
    {
        return $this->getTopKeyWordsWithOrWithoutHits($rootPageId, $limit, true);
    }

    /**
     * Find Top search keywords with results
     *
     * @param int $rootPageId
     * @param int $limit
     * @param bool $withoutHits
     *
     * @return string
     */
    protected function getTopKeyWordsWithOrWithoutHits($rootPageId, $limit, $withoutHits)
    {
        $rootPageId = (int) $rootPageId;
        $limit = (int) $limit;
        $withoutHits = (bool) $withoutHits;

        // Check if we want without or with hits
        if ($withoutHits === true) {
            $comparisonOperator = '=';
        } else {
            $comparisonOperator = '>';
        }

        // Query database
        $statisticsRows = $this->getDatabase()->exec_SELECTgetRows(
            'keywords, count(keywords) as count, num_found as hits',
            'tx_solr_statistics',
            'root_pid = ' . $rootPageId . ' and num_found ' . $comparisonOperator . ' 0',
            'keywords, num_found',
            'count DESC, hits DESC, keywords ASC',
            $limit
        );

        return $statisticsRows;
    }

    /**
     * @return \TYPO3\CMS\Core\Database\DatabaseConnection
     */
    protected function getDatabase()
    {
        return $GLOBALS['TYPO3_DB'];
    }
}
