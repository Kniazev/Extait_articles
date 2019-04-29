<?php

namespace Extait\Articles\Console\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Magento\Framework\App\State;
use Magento\Framework\App\Area;
use Magento\Framework\Console\Cli;
use Extait\Articles\Model\ArticleMonthMailing;
use Magento\Framework\Exception\LocalizedException;

/**
 * Send email from command line
 * Class SendEmailCommand
 * @package Extait\Articles\Console\Command
 */
class SendEmailCommand extends Command
{
    const EMAIL = 'e';
    const MONTH = 'm';
    const XML_PATH_ARTICLE_MAIL_HEADER = 'articles/seo/article_mail_header';

    /**
     * @var State
     */
    protected $state;

    /**
     * @var ArticleMonthMailing
     */
    protected $mailing;

    /**
     * SendEmailCommand constructor.
     * @param State $state
     * @param ArticleMonthMailing $mailing
     */
    public function __construct(
        State $state,
        ArticleMonthMailing $mailing
    ) {
        $this->mailing = $mailing;
        $this->state = $state;
        parent::__construct();
    }

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this->addArgument(self::EMAIL, InputArgument::REQUIRED);
        $this->addArgument(self::MONTH, InputArgument::OPTIONAL);

        $this->setName('extait:digest:send')
            ->setDescription('Send test ');
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|null
     * @throws LocalizedException
     */
    public function execute(InputInterface $input, OutputInterface $output)
    {
        $this->state->setAreaCode(Area::AREA_FRONTEND);

        try {

            $receiver = $input->getArgument(self::EMAIL);
            $month = $input->getArgument(self::MONTH);

            if (($month < 1 || $month > 12) && $month != null) {
                $output->writeln('You are on Earth');
                return Cli::RETURN_FAILURE;
            }

            $articles['list'] = $this->mailing->getArticlesList($month);

            if($articles['list'] == null) {
                $output->writeln('Articles for '. $this->mailing->getPreviousMonthInWord($month) . ' are not exist.');
                return Cli::RETURN_FAILURE;
            }

            $this->mailing->sendEmail($articles, $receiver);
            return Cli::RETURN_SUCCESS;
        } catch (\Exception $e) {
            return Cli::RETURN_FAILURE;
        }
    }
}
