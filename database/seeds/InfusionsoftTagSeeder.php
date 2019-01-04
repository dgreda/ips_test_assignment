<?php

declare(strict_types=1);

use App\Services\InfusionsoftClient;
use App\Tag;
use Illuminate\Database\Seeder;
use App\Module;

class InfusionsoftTagSeeder extends Seeder
{
    /**
     * @var InfusionsoftClient
     */
    private $infusionsoftClient;

    /**
     * @param InfusionsoftClient $infusionsoftClient
     */
    public function __construct(InfusionsoftClient $infusionsoftClient)
    {
        $this->infusionsoftClient = $infusionsoftClient;
    }

    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(): void
    {
        $infusionsoftTags = $this->infusionsoftClient->getAllTags()->all();
        foreach ($infusionsoftTags as $infusionsoftTag) {
            if (!empty($infusionsoftTag['category'])) {
                continue;
            }

            $tag         = new Tag;
            $tag->tag_id = $infusionsoftTag['id'];
            $tag->name   = $infusionsoftTag['name'];
            $tag->save();

            $moduleName = str_replace('Start', '', $infusionsoftTag['name']);
            $moduleName = trim(str_replace('Reminders', '', $moduleName));

            Module::where('name', $moduleName)->update(['tag_id' => $tag->id]);
        }
    }
}
