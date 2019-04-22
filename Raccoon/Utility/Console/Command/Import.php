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
                    $output->writeln("Attribute code is not given.");
                    break;
                }

                if (! isset($_filename)) {
                    $output->writeln("Filename is not given.");
                    break;
                }

                if (! file_exists($_filename)) {
                    $output->writeln("'$_filename' doesn't exist.");
                    break;
                }

                $_fin = fopen($_filename, 'r');
                if (empty($_fin)) {
                    $output->writeln("'$_filename' cannot be opened.");
                    break;
                }

                $_object_manager = \Magento\Framework\App\ObjectManager::getInstance();
                $_store_manager = $_object_manager->get('Magento\Store\Model\StoreManagerInterface');
                $_stores = $_store_manager->getStores();
                $_stores_array = [];
                // $_stores_array[0] = 'All Store Views';
                foreach ($_stores as $_store) {
                    $_stores_array[$_store->getId()] = $_store->getName();
                }
            
                $_options = [];
                $_options['value'] = [];
                while (($_data = fgetcsv($_fin, 1000, ',')) !== FALSE) {
                    $_value = $_data[0];
                    $_label = $_data[1];
                    $_options['value'][$_label][0] = $_value;

                    foreach ($_stores_array as $_store_id => $_store_name) {
                        $_options['value'][$_label][$_store_id] = $_label;
                    }
                }

                fclose($_fin);

                
                $_eav_config = $_object_manager->get('\Magento\Eav\Model\Config');
                $_attribute = $_eav_config->getAttribute('catalog_product', $_attribute_code);
                $_attribute_id = $_attribute->getAttributeId();
                
                if (! isset($_attribute_id)) {
                    $output->writeln("Attribute code '$_attribute_code' is not found.");
                    break;
                }

                $_options['attribute_id'] = $_attribute_id;

                $_eav_setup = $_object_manager->get('\Magento\Eav\Setup\EavSetup');
                $_eav_setup->addAttributeOption($_options);

                break;
            default:
                $output->writeln("Type '$_type' not found.");
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
