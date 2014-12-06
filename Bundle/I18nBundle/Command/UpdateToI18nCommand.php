<?php
namespace Victoire\Bundle\I18nBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Victoire\Bundle\I18nBundle\Entity\I18n;
use Symfony\Component\Console\Input\ArrayInput;

/**
 * Update a site to be compatible with i18n
 */
class UpdateToI18nCommand extends ContainerAwareCommand
{
    /**
    * this method is the configuration of the command
    */
	protected function configure()
    {
        $this
            ->setName('victoire:migrate:i18n')
            ->setDescription('updater la base de donnée en I18n')
            ->addArgument('default-locale', InputArgument::OPTIONAL, 'Quelle est la langue de votre site?')
        ;
    }

    /**
    * @param InputInterface $input
    * @param OutpuInterface $output
    *
    * this method is executed when we launch the command
    */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $defaultLocale = $input->getArgument('default-locale');
        $defaultLocale = $defaultLocale ? $defaultLocale : 'fr';
        $this->doUpdate($output, $defaultLocale);
    }

    /**
    * @param OutputInterface $output
    * @param $locale the current locale of the application
    * 
    * this method allow you to update the schema with the new i18n entity and its relations.
    *  it also sets all your views to the locale given in parameter and build the links
    */
    protected function doUpdate(OutputInterface $output, $locale) 
    {
    	$container = $this->getApplication()->getKernel()->getContainer();

        $em = $container->get('doctrine.orm.entity_manager');


        $command = $this->getApplication()->find('doctrine:schema:update');
        $doctrineInput = new ArrayInput(
                                        array(
                                            'command' => 'doctrine:schema:update',
                                            '--force' => true
                                            )
                                        );
        $command->run($doctrineInput, $output);
    	$views = $em->getRepository('VictoireCoreBundle:View')->findAll();

    	foreach ($views as $view) {
    		$view->setLocale($locale);
            $i18n = new I18n();
            $em->persist($i18n);
            $i18n->setTranslation($locale, $view);
    		$view->setI18n($i18n);
    		$em->persist($view);
    	}

    	$em->flush();
        $this->doGenerateViewCache($output);
    }
    /**
    * @param OutputInterface $output
    *
    * this call the command victoire:generate:view-cache
    */
    protected function doGenerateViewCache(OutputInterface $output) 
    {
        $command = $this->getApplication()->find('victoire:generate:view-cache');
        $doctrineInput = new ArrayInput(
                                        array(
                                            'command' => 'victoire:generate:view-cache'
                                            )
                                        );
        $command->run($doctrineInput, $output);
    }
}