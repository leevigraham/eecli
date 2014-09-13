<?php

namespace eecli\Command;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputArgument;

class DeleteTemplateCommand extends Command
{
    /**
     * {@inheritdoc}
     */
    protected $name = 'delete:template';

    /**
     * {@inheritdoc}
     */
    protected $description = 'Delete one or more templates.';

    /**
     * {@inheritdoc}
     */
    protected function getArguments()
    {
        return array(
            array(
                'template', // name
                InputArgument::IS_ARRAY | InputArgument::REQUIRED, // mode
                'Template name(s) (ex. site/index)', // description
            ),
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function fire()
    {
        $templates = $this->argument('template');

        $this->getApplication()->newInstance('\\eecli\\CodeIgniter\\Controller\\DesignController');

        ee()->load->model('template_model');
        ee()->template = ee()->TMPL;

        foreach ($templates as $template) {

            if (! preg_match('#^[a-zA-Z0-9_\-]+/[a-zA-Z0-9_\-]+$#', $template)) {
                $this->error('Template '.$template.' must be in <template_group>/<template_name> format.');

                continue;
            }

            list($groupName, $templateName) = explode('/', $template);

            $query = ee()->db->select('template_id')
                ->join('template_groups', 'template_groups.group_id = templates.group_id')
                ->where('group_name', $groupName)
                ->where('template_name', $templateName)
                ->get('templates');

            if ($query->num_rows() === 0) {
                $this->error('Template '.$template.' not found.');
            } else {
                $_POST = array(
                    'template_id' => $query->row('template_id'),
                );

                ee()->template_delete();

                $this->info('Template '.$template.' deleted.');
            }

            $query->free_result();
        }
    }
}
