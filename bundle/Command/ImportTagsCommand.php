<?php

declare(strict_types=1);

namespace Netgen\TagsBundle\Command;

use eZ\Publish\API\Repository\Repository;
use eZ\Publish\API\Repository\Values\Content\Language;
use eZ\Publish\Core\Repository\Values\User\UserReference;
use Netgen\TagsBundle\API\Repository\TagsService;
use Netgen\TagsBundle\API\Repository\Values\Tags\Tag;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Serializer;
use RuntimeException;

class ImportTagsCommand extends Command
{
    /** @var \eZ\Publish\API\Repository\Repository  */
    private $repository;

    /** @var \Symfony\Component\Serializer\Serializer  */
    private $serializer;

    /** @var \Netgen\TagsBundle\API\Repository\TagsService  */
    private $tagsService;

    /** @var \Symfony\Component\Console\Style\SymfonyStyle  */
    private $style;

    public function __construct(
        Repository $repository,
        Serializer $serializer,
        TagsService $tagsService
    )
    {
        $this->repository = $repository;
        $this->serializer = $serializer;
        $this->tagsService = $tagsService;

        parent::__construct(null);
    }

    protected function configure()
    {
        $this
            ->setName('netgen:tags:import')
            ->setDescription("Creates new tags based on an already existing CSV file.")
            ->setHelp("This command creates new tags based on an already existing CSV file. The filename parameter is required and should contain the full path of the file. The parent-tag-id parameter is optional nd should contain the ID of the tag underneath which to add the newly created tags. 
            
            The file headers should be language codes. The script supports an additional header 'RemoteID', which sets the remote id value of the tag imported - this is particularly useful when connecting tags from a different system.")
            ->addOption(
                'filename',
                null,
                InputOption::VALUE_REQUIRED,
                'The CSV file to be imported (NOTE: input the full path of the file)'
            )
            ->addOption(
                'parent-tag-id',
                null,
                InputOption::VALUE_OPTIONAL,
                'The ID of the tag underneath which to add the tags in the CSV file'
            );
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->style = new SymfonyStyle($input, $output);

        $this->style->title('Tags import');

        $adminUserReference = new UserReference(14);

        // We need to set the current user as admin since the anonymous user (which is logged in by default)
        // does not have permission to add a tag.
        $this->repository->getPermissionResolver()->setCurrentUserReference($adminUserReference);

        $filename = $input->getOption('filename');

        if (empty($filename) {
            throw new RuntimeException('Parameter --filename is required');
        }

        $parentTagId = $input->getOption('parent-tag-id');

        // If the parent-tag-id parameter is not specified, the parent tag ID is set to the root tag ID.
        if (empty($parentTagId) {
            $parentTagId = 0;
        }

        if (!file_exists($filename)) {
            throw new RuntimeException('No file has been found on the specified location.');
        }

        $this->style->text('Found file on the specified location.');

        // See RFC 7111 for clarification on text/csv MIME type usage
        if (!mime_content_type($filename) == 'text/csv') {
            throw new RuntimeException('The file is not a valid CSV.');
        }

        $fileContents = file_get_contents($filename);

        $data = $this->serializer->decode($fileContents, 'csv');

        if (empty($data)) {
            throw new RuntimeException('No data found.');
        }

        $dataCount = count($data);

        $this->style->text("Found <comment>{$dataCount}</comment> tags for import from the file.");

        $availableLanguageCodes = array_map(
            function(Language $language) {
                return $language->languageCode;
            },
            $this->repository->getContentLanguageService()->loadLanguages()
        );

        // IMPORTANT: the mainLanguageCode for the tag is always presumed to be the first language in the list.
        $mainLanguageCode = $availableLanguageCodes[0];

        $this->style->progressStart($dataCount);

        foreach ($data as $datum) {
            $tagCreateStruct = $this->tagsService->newTagCreateStruct($parentTagId, $mainLanguageCode);

            if (array_key_exists('RemoteID', $datum)) {
                try {
                    $tag = $this->tagsService->loadTagByRemoteId($datum['RemoteID']);
                } catch (\eZ\Publish\API\Repository\Exceptions\NotFoundException $e) {
                    $tagCreateStruct->remoteId = $datum['RemoteID'];
                }

                if (isset($tag) && $tag instanceof Tag) {
                    $this->style->text("Found already existing tag with remote ID <comment>{$tag->remoteId}</comment>.");
                    $this->style->progressAdvance();
                    unset($tagCreateStruct);
                    continue;
                }
            }

            foreach($availableLanguageCodes as $availableLanguageCode) {
                if (array_key_exists($availableLanguageCode, $datum)) {
                    $tagCreateStruct->setKeyword($datum[$availableLanguageCode], $availableLanguageCode);
                }
            }

            $this->style->progressAdvance();
        }

        $this->style->progressFinish();

        $this->style->text("Import complete.");
    }
}
