<?php

namespace Netgen\TagsBundle\Command;

use Netgen\TagsBundle\API\Repository\Values\Tags\TagCreateStruct;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class ImportTagsCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('netgen:tags:import')
            ->setDescription("Creates new tags based on an already existing CSV file. The file should be uploaded to the root of the installation.")
            ->addOption(
                'filename',
                null,
                InputOption::VALUE_REQUIRED,
                'The CSV file to be imported (NOTE: input the relative path from the root of the installation)'
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
        /** @var \eZ\Publish\API\Repository\Repository $repository
         */
        $repository = $this->getContainer()->get('ezpublish.api.repository');

        $currentUserReference = $repository->getPermissionResolver()->getCurrentUserReference();

        $adminUserReference = new UserReference(14);

        // We need to set the current user to admin until the import is ready since the anonymous user which is logged
        // in by default does not have a permission to add a tag.
        $repository->getPermissionResolver()->setCurrentUserReference($adminUserReference);

        $filename = $input->getOption('filename');

        if (empty($filename)) {
            throw new \RuntimeException('Parameter --filename is required');
        }

        $parentTagId = $input->getOption('parent-tag-id');

        if (empty($parentTagId)) {
            $parentTagId = 0;
        }

        $fileFullPath = $this->getContainer()->getParameter('kernel.root_dir') . $filename;

        if (!file_exists($fileFullPath)) {
            throw new \RuntimeException("No file has been found on the specified location.");
        }

        $output->writeln('Found file on the specified location.');

        $fileContents = file_get_contents($fileFullPath);

        /** @var \Symfony\Component\Serializer\Serializer $serializer */
        $serializer = $this->getContainer()->get('serializer');

        $data = $serializer->decode($fileContents, 'csv');

        $dataCount = count($data);

        $output->writeln("Found <comment>{$dataCount}</comment> tags for import from the file.");

        // We support only locale headers and the RemoteID header for now
        $headers = array_keys($data[0]);

        $localeList = array();

        $output->writeln('Extracting the locale list.');

        foreach ($headers as $header) {
            if ($header !== 'RemoteID') {
                $localeList[] = $header;
            }
        }

        $output->writeln('List of locales extracted.');

        // IMPORTANT: the mainLanguageCode for the tag is always presumed to be the first language in the list.
        $mainLanguageCode = $localeList[0];

        $existing = array();

        $progress = new ProgressBar($output, $dataCount);

        foreach ($data as $datum) {
            if (in_array('RemoteID', $headers)) {
                $remoteID = $datum['RemoteID'];

                try {
                    /** @var \Netgen\TagsBundle\API\Repository\Values\Tags\Tag $tag */
                    $tag = $tagsService->loadTagByRemoteId($remoteID);
                } catch (\eZ\Publish\API\Repository\Exceptions\NotFoundException $e) {
                    $this->createTag($datum, $mainLanguageCode, $localeList, $parentTagId);
                }

                if (isset($tag) && ($tag instanceof Netgen\TagsBundle\API\Repository\Values\Tags\Tag))
                {
                    $existing[] = $tag->remoteId;
                    $progress->advance();
                    continue;
                }

                $this->createTag($datum, $mainLanguageCode, $localeList, $parentTagId);
                $progress->advance();
            }
        }

        $progress->finish();

        $output->writeln('');

        $output->writeln("The import is complete.");

        if (count($existing) > 0) {
            $existingCount = count($existing);

            $existingIds = implode(', ', $existing);

            $output->writeln("<comment>{$existingCount}</comment> tags already exist. The remote IDs in question are: {$existingIds}");
        }

        $repository->getPermissionResolver()->setCurrentUserReference($currentUserReference);

        return 0;
    }

    private function createTag($datum, $mainLanguageCode, $localeList, $parentTagId)
    {
        /** @var \Netgen\TagsBundle\API\Repository\TagsService $tagsService */
        $tagsService = $this->getContainer()->get('ezpublish.api.service.tags');

        $tagCreateStruct = new TagCreateStruct();

        if (array_key_exists('RemoteID', $datum)) {
            $tagCreateStruct->remoteId = $datum['RemoteID'];
        }

        foreach ($localeList as $locale) {
            if ($locale == $mainLanguageCode) {
                $tagCreateStruct->mainLanguageCode = $mainLanguageCode;
            }

            $tagCreateStruct->setKeyword($datum[$locale], $locale);
        }

        return $tagsService->createTag($tagCreateStruct);
    }
}