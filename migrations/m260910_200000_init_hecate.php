<?php

use yii\db\Migration;

class m260910_200000_init_hecate extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%division}}', [
            'id' => $this->primaryKey(),
            'code' => $this->string(32)->notNull()->unique(),
            'name' => $this->string(120)->notNull(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createTable('{{%printer}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(160)->notNull()->unique(),
            'host' => $this->string(160)->notNull(),
            'vendor' => $this->string(160),
            'model' => $this->string(160),
            'location' => $this->string(160),
            'is_color' => $this->boolean()->notNull()->defaultValue(false),
            'is_duplex' => $this->boolean()->notNull()->defaultValue(false),
            'monitor_source' => $this->string(40),
            'last_seen_at' => $this->timestamp(),
            'enabled' => $this->boolean()->notNull()->defaultValue(true),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);

        $this->createTable('{{%quota}}', [
            'id' => $this->primaryKey(),
            'division_id' => $this->integer()->notNull(),
            'period' => $this->string(7)->notNull(),
            'bw_allocated' => $this->integer()->notNull()->defaultValue(0),
            'bw_used' => $this->integer()->notNull()->defaultValue(0),
            'bw_reserved' => $this->integer()->notNull()->defaultValue(0),
            'color_allocated' => $this->integer()->notNull()->defaultValue(0),
            'color_used' => $this->integer()->notNull()->defaultValue(0),
            'color_reserved' => $this->integer()->notNull()->defaultValue(0),
        ]);

        $this->addForeignKey(
            'fk_quota_division',
            '{{%quota}}',
            'division_id',
            '{{%division}}',
            'id',
            'CASCADE'
        );
        $this->createIndex(
            'uq_quota_division_period',
            '{{%quota}}',
            ['division_id', 'period'],
            true
        );

        $this->createTable('{{%contract}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(160)->notNull(),
            'type' => $this->string(24)->notNull(),
            'bw_quota' => $this->integer(),
            'color_quota' => $this->integer(),
            'bw_unit_price' => $this->decimal(12, 4),
            'color_unit_price' => $this->decimal(12, 4),
            'bw_overage_price' => $this->decimal(12, 4),
            'color_overage_price' => $this->decimal(12, 4),
            'active' => $this->boolean()->notNull()->defaultValue(true),
        ]);

        $this->createTable('{{%audit_log}}', [
            'id' => $this->bigPrimaryKey(),
            'actor' => $this->string(80),
            'action' => $this->string(120)->notNull(),
            'entity' => $this->string(80),
            'entity_id' => $this->string(80),
            'details' => $this->text(),
            'created_at' => $this->timestamp()->defaultExpression('CURRENT_TIMESTAMP'),
        ]);
    }

    public function safeDown()
    {
        $this->dropTable('{{%audit_log}}');
        $this->dropTable('{{%contract}}');
        $this->dropTable('{{%quota}}');
        $this->dropTable('{{%printer}}');
        $this->dropTable('{{%division}}');
    }
}
