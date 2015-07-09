<?php

namespace eecli\Command;

use eecli\Command\Contracts\HasExamples;
use eecli\Command\Contracts\HasLongDescription;
use eecli\Command\Contracts\HasOptionExamples;
use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputOption;

class CreateChannelEntryCommand extends Command implements HasExamples, HasOptionExamples, HasLongDescription
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'create:channel_entry';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Create a channel entry.';

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array(
            array(
                'title',
                InputArgument::REQUIRED,
                'What is the title of the channel entry? (ex. Privacy Policy)',
            ),
            array(
                'channel',
                InputArgument::REQUIRED,
                'What is the channel (channel ID or short name) this entry belongs to?',
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function getOptions()
    {
        return array(
            array(
                'url_title',
                'u',
                InputOption::VALUE_REQUIRED,
                'Does this entry require a custom URL title?', // description
                '',
            ),
            array(
                'status',
                's',
                InputOption::VALUE_REQUIRED,
                'What status will the channel entry have when it\'s created?',
                '',
            ),
            array(
                'categories',
                'c',
                InputOption::VALUE_REQUIRED | InputOption::VALUE_IS_ARRAY,
                'Which categories (ID or cat_url_title) do you want to assign this channel entry to?',
            ),
            array(
                'author',
                'a',
                InputOption::VALUE_REQUIRED,
                'What is the author (member ID or username) for this channel?',
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        // OK, so we can't do this the same as the other controller functions as the Content_publish class has a
        // private property (`_assigned_channels`) that is important to have instantiated otherwise the `entry_form()`
        // will not work and result in a permissions related error (boo!). So its back to the API method again.

        // load the required EE libraries
        ee()->load->helper('security');

        // load a super admin into the session
        $query = ee()->db->where('members.group_id', 1)
            ->join('member_groups', 'member_groups.group_id = members.group_id')
            ->limit(1)
            ->get('members');

        // superadmin
        ee()->session->userdata = $query->row_array();
        ee()->session->userdata['group_id'] = '1';
        ee()->session->userdata['assigned_template_groups'] = array();

        $query->free_result();

        // check first to avoid debug messages
        if (false === isset(ee()->api)) {
            ee()->load->library(array('api', 'stats'));
            ee()->api->instantiate('channel_fields');
            ee()->api->instantiate('channel_entries');
        }

        $titleParam = $this->argument('title');
        $channelParam = $this->argument('channel');
        $urlTitleParam = $this->option('url_title');
        $authorParam = $this->option('author');
        $statusParam = $this->option('status');

        // get the channel
        $channel = $this->getChannel($channelParam);
        $channelId = $channel['channel_id'];

        // was an author specified?
        if ($authorParam) {
            $author = $this->getAuthor($authorParam);
            $authorId = $author['member_id'];
        } else {
            // user the super admin as the default author
            $authorId = ee()->session->userdata('member_id');
        }

        $categories = array();
        $categoriesParam = $this->option('categories');

        // were categories specified?
        if (count($categoriesParam) > 0) {
            $categories = $this->getCategories($categoriesParam);
        }

        // set a default date
        $nowDate = ee()->localize->format_date('%Y%m%d%H%i%s', ee()->localize->now);

        // build up an array the article data using the field names for convenience
        $metaData = array(
            'title' => $titleParam,
            'url_title' => $urlTitleParam,
            'status' => $statusParam,
            'entry_date' => strtotime($nowDate),
            'edit_date' => $nowDate,
            'expiration_date' => null,
            'author_id' => (int) $authorId,
            'category' => (array) $categories,
            'channel_id' => $channelId,
        );

        $entryData = array();
        $entryFinalData = $metaData;

        // get the field mapping
        $fieldMapping = $this->getFields();
        $fieldMappingKeys = array_keys($fieldMapping);

        // loop over the mapped data and create a new array using the field_id_# as the array key ready for API saving
        foreach ($entryData as $entryDataKey => $entryDataVal) {
            if (true === in_array($entryDataKey, $fieldMappingKeys)) {
                $entryFinalData['field_id_' . $fieldMapping[$entryDataKey]] = $entryDataVal;
            } else {
                $entryFinalData[$entryDataKey] = $entryDataVal;
            }
        }

        // prep the channel fields API for saving
        ee()->api_channel_fields->setup_entry_settings($channelId, $entryFinalData);

        // save the entry using the API
        if (false === ee()->api_channel_entries->save_entry($entryFinalData, $channelId)) {

            throw new \RuntimeException('Could not create the new entry: ' . "\n\n" . implode("\n", ee()->api_channel_fields->errors));
        }

        $this->getApplication()->checkForErrors(true);

        $this->info(sprintf('Channel Entry "%s" created in "%s" channel.', $titleParam, $channel['channel_name']));
    }

    public function getLongDescription()
    {
        return 'Create a channel entry. Pass in an entry title wrapped in quotes and optionally pass in a url title using underscores only. If you exclude the url title, one will be auto-generated from your entry title.';
    }

    public function getExamples()
    {
        return array(
            /*
            'Create a channel with the short name test_channel' => 'test_channel',
            'Create a channel with the title Test Channel' => 'test_channel "Test Channel"',
            'Create a channel with field group 5' => '--field_group=5 test_channel',
            'Create a channel with field group "Blog"' => '--field_group="Blog" test_channel',
            'Create a channel with status group "Statuses"' => '--status_group="Statuses" test_channel',
            'Create a channel with category group 5 and 6' => '--cat_group=5 --cat_group=6 test_channel',
            'Create a channel with category group "Apparel" and "Accessories"' => '--cat_group="Apparel" --cat_group="Accessories" test_channel',
            'Create a channel with new field group with same title as channel' => '--new_field_group test_channel',
             */
        );
    }

    public function getOptionExamples()
    {
        return array(
            /*
            'field_group' => '1',
            'status_group' => '1',
            'cat_group' => '1',
            'channel_url' => '/blog',
            'channel_description' => 'Your description here.',
            'default_entry_title' => 'Default Title',
            'url_title_prefix' => 'blog_',
            'deft_category' => '1',
             */
        );
    }


    /**
     * Find the matching categories using the parameters
     *
     * @param array $categoriesParam
     *
     * @return array
     */
    protected function getCategories($categoriesParam)
    {
        $categories = array();

        // get the author if using the parameter
        $query = ee()->db->select('cat_id, cat_url_title')
            ->where_in('cat_id', $categoriesParam)
            ->or_where_in('cat_url_title', $categoriesParam)
            ->get('categories');

        // no member found? quit now
        if (0 === $query->num_rows()) {
            throw new \RuntimeException(sprintf('Could not find categories for url titles or IDs "%s"', implode(', ', $categoriesParam)));

            return false;
        }

        // add the valid categories to the collection
        foreach ($query->result_array() as $categoryRow) {
            $categories[] = $categoryRow['cat_id'];
        }

        return $categories;
    }

    /**
     * Find the matching member using the parameters
     *
     * @param array $authorParam
     *
     * @return array
     */
    protected function getAuthor($authorParam)
    {
        $author = array();

        // get the author if using the parameter
        $query = ee()->db->select('member_id, username')
            ->where('member_id', intval($authorParam))
            ->or_where('username', $authorParam)
            ->limit(1)
            ->get('members');

        // no member found? abort
        if (0 === $query->num_rows()) {
            throw new \RuntimeException(sprintf('Could not find member for username or ID "%s"', $authorParam));
        }

        $author = $query->row_array();

        $query->free_result();

        return $author;
    }

    /**
     * Find the matching channel using the parameters
     *
     * @param int|string $channelParam
     *
     * @return array
     */
    protected function getChannel($channelParam)
    {
        $channel = array();

        // get the channel
        $query = ee()->db->select('channel_id, channel_name')
            ->where('channel_id', intval($channelParam))
            ->or_where('channel_name', $channelParam)
            ->limit(1)
            ->get('channels');

        // no channel found? quit now
        if (0 === $query->num_rows()) {
            throw new \RuntimeException(sprintf('Could not find channel for short name or ID "%s"', $channelParam));
        }

        $channel = $query->row_array();

        $query->free_result();

        return $channel;
    }

    /**
     * Get all fields from the database for now as key-value pairs
     *
     * @return array
     */
    protected function getFields()
    {
        $fields = array();

        // get the field names and ids
        $query = ee()->db->select('field_id, field_name')
            ->get('channel_fields');

        $fields = array();
        foreach ($query->result_array() as $fieldRow) {
            $fields[$fieldRow['field_id']] = $fieldRow['field_name'];
        }

        $query->free_result();

        return $fields;
    }

}

