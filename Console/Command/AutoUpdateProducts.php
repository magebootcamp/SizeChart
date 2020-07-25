<?php
/**
 * Copyright (c) MageBootcamp 2020.
 *
 * Created by MageBootcamp: The Ultimate Online Magento Course.
 * We are here to help you become a Magento PRO.
 * Watch and learn at https://magebootcamp.com.
 *
 * @author Daniel Donselaar
 */
namespace MageBootcamp\SizeChart\Console\Command;

use MageBootcamp\SizeChart\Model\ProductSizeUpdater;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * This command updates the sizes based on the predefined sizes in the di.xml
 * Instead of injecting fixed sizes based on the 'size' attribute you can also consider
 * creating a view in the backend that the admin user can manage the sizes for a category.
 */
class AutoUpdateProducts extends Command
{
    /**
     * @var \MageBootcamp\SizeChart\Model\ProductSizeUpdater
     */
    protected $productSizeUpdater;

    /**
     * @param \MageBootcamp\SizeChart\Model\ProductSizeUpdater $productSizeUpdater
     * @param string|null                                      $name
     */
    public function __construct(
        ProductSizeUpdater $productSizeUpdater,
        string $name = null
    ) {
        parent::__construct($name);

        $this->productSizeUpdater = $productSizeUpdater;
    }

    /**
     * Configure sets the name and description of the command.
     */
    protected function configure()
    {
        $this->setName('magebootcamp:sizes:update');
        $this->setDescription('Automatically updates product sizes based on category settings of MySizes');
        parent::configure();
    }

    /**
     * The execute command updates the product sizes based on the category configuration.
     *
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @return void
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("<info>Updating products sizes based on the size chart<info>");
        $this->productSizeUpdater->update($output);
        $output->writeln('<info>Done<info>');
    }
}
