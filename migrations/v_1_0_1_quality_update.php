<?php
namespace bruno\mopedgarage\migrations;

class v_1_0_1_quality_update extends \phpbb\db\migration\migration
{
    public function effectively_installed()
    {
        return isset($this->config['mopedgarage_version']) && version_compare($this->config['mopedgarage_version'], '1.0.1', '>=');
    }

    static public function depends_on()
    {
        return ['\bruno\mopedgarage\migrations\v100'];
    }

    public function update_data()
    {
        return [
            ['config.update', ['mopedgarage_version', '1.0.1']],
            ['config.add', ['mopedgarage_enable_gallery', 1]],
            ['config.add', ['mopedgarage_lightbox_global', 1]],
            ['config.add', ['mopedgarage_mobile_card_scale', 'compact']],
        ];
    }

    /**
     * Charset conversion is board-specific. We do not execute blind ALTER statements here,
     * because table names can differ between interim builds. Instead, this migration upgrades
     * config flags and ships a checked SQL helper list in docs/.
     */
}
