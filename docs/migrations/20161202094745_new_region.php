<?php

use Phinx\Migration\AbstractMigration;

class NewRegion extends AbstractMigration
{
    public function change()
    {
        $this->table(PSM_DB_PREFIX."servers_uptime")
            ->addColumn("region", "char", ["length"=>"3", "default"=>"127"])
            ->addIndex("region")
            ->save();
        $this->table(PSM_DB_PREFIX."servers_history")
            ->addColumn("region", "char", ["length"=>"3", "default"=>"127"])
            ->addIndex("region")
            ->save();
    }
}
