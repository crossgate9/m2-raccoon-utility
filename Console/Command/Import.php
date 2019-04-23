<?php

namespace Raccoon\Utility\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class Import extends Command {

    const TYPE_ARGUMENT = "type";
    const OPTION1_ARGUMENT = "option_1";
    const OPTION2_ARGUMENT = "option_2";

    /**
     * {@inheritdoc}
     */
    protected function execute(
        InputInterface $input,
        OutputInterface $output
    ) {
        $_type = $input->getArgument(self::TYPE_ARGUMENT);
        
        switch ($_type) {
            case 'attribute_values':
                // import attribute_values;
                $_attribute_code = $input->getArgument(self::OPTION1_ARGUMENT);
                $_filename = $input->getArgument(self::OPTION2_ARGUMENT);

                if (! isset($_attribute_code)) {
                    $output->writeln("[error] Attribute code is not given.");
                    break;
                }

                if (! isset($_filename)) {
                    $output->writeln("[error] Filename is not given.");
                    break;
                }

                if (! file_exists($_filename)) {
                    $output->writeln("[error] '$_filename' doesn't exist.");
                    break;
                }

                $_fin = fopen($_filename, 'r');
                if (empty($_fin)) {
                    $output->writeln("[error] '$_filename' cannot be opened.");
                    break;
                }

                // load store information
                $_object_manager = \Magento\Framework\App\ObjectManager::getInstance();
                $_store_manager = $_object_manager->get('Magento\Store\Model\StoreManagerInterface');
                $_stores = $_store_manager->getStores();
                $_stores_array = [];
                $_stores_array[0] = 'All Store Views';
                foreach ($_stores as $_store) {
                    $_stores_array[$_store->getId()] = $_store->getName();
                }

                // first line of csv file should be header
                $_has_admin_setting = false;
                $_header = fgetcsv($_fin, 1000, ',');
                foreach ($_header as $_column) {
                    // each column should be numberic as store view id
                    if (! is_numeric($_column)) {
                        $output->writeln("[error] '$_filename' header $_column is not correct.");
                        die();
                    }

                    if (isset($_stores_array[$_column])) {
                        $output->writeln("[info] Store view $_column settings found.");
                    } else {
                        $output->writeln("[info] None existing store view $_column settings found.");
                    }
                    
                    // admin column found.
                    if ($_column === '0') {
                        $_has_admin_setting = true;    
                    }
                }

                if ($_has_admin_setting === false) {
                    $_output->writeln('[error] Admin settings is not found.');
                    break;
                }
            
                // process csv file to get all options
                $_options = [];
                $_options['value'] = [];
                while (($_data = fgetcsv($_fin, 1000, ',')) !== FALSE) {
                    // determine the label with the first none-admin column
                    $_label = false;
                    foreach ($_data as $_idx => $_value) {
                        $_store_id = $_header[$_idx];
                        // must be none admin and store view exists
                        if ($_store_id != 0 && isset($_stores_array[$_store_id])) {
                            $_label = $_value;
                        }
                    }

                    if ($_label === false) {
                        $_output->writeln('[error] Error occurred.');
                        die();
                    }

                    $_options['value'][$_label] = [];

                    foreach ($_data as $_idx => $_value) {
                        $_store_id = $_header[$_idx];
                        if (isset($_stores_array[$_store_id])) {
                            $_options['value'][$_label][$_store_id] = $_value;
                        }
                    }
                }
                fclose($_fin);

                // load attribute model
                $_eav_config = $_object_manager->get('\Magento\Eav\Model\Config');
                $_attribute = $_eav_config->getAttribute('catalog_product', $_attribute_code);
                $_attribute_id = $_attribute->getAttributeId();
                
                if (! isset($_attribute_id)) {
                    $output->writeln("[error] Attribute code '$_attribute_code' is not found.");
                    break;
                }

                $_options['attribute_id'] = $_attribute_id;

                // save
                $_eav_setup = $_object_manager->get('\Magento\Eav\Setup\EavSetup');
                $_eav_setup->addAttributeOption($_options);

                break;
            default:
                $output->writeln("[error] Type '$_type' not found.");
                break;
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configure() {
        $this->setName("raccoon_utility:import");
        $this->setDescription("Import Utilities");
        $this->setDefinition([
            new InputArgument(self::TYPE_ARGUMENT, InputArgument::REQUIRED, 'Type'),
            new InputArgument(self::OPTION1_ARGUMENT, InputArgument::OPTIONAL, "Option 1"),
            new InputArgument(self::OPTION2_ARGUMENT, InputArgument::OPTIONAL, "Option 2"),
        ]);
        parent::configure();
    }
}