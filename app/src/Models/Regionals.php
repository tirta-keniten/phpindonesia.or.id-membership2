<?php
namespace Membership\Models;

use Membership\Models;

class Regionals extends Models
{
    private $table = 'regionals';

    public function getProvices()
    {
        $stmt = $this->db->select(['id', 'regional_name'])
            ->from($this->table)
            ->whereNull('parent_id')
            ->where('city_code', '=', '00')
            ->orderBy('city_code');

        return $stmt->execute()->fetchAll();
    }

    public function getCities($provinceId)
    {
        $stmt = $this->db->select(['id', 'regional_name'])
            ->from($this->table)
            ->where('parent_id', '=', $provinceId)
            ->orderBy('city_code');

        return $stmt->execute()->fetchAll();
    }
}