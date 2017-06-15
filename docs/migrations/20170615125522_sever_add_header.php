<?php

use Phinx\Migration\AbstractMigration;

class SeverAddHeader extends AbstractMigration
{
    public function change()
    {
        $this->table(PSM_DB_PREFIX."servers")
        ->addColumn("headers", "string", ["length"=>"500", "default"=>"", "comment"=>"curl headers, split with ;", "after"=>"header_value"])
        ->save();
    }
}
