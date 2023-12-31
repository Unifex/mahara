{if $record->deleted}
    <h2 class="title list-group-item-heading text-inline">
        <span class="icon icon-file left text-midtone" role="presentation" aria-hidden="true"></span>
        {$record->title}
        <span class="artefacttype text-midtone text-regular">({str tag=deleted section=search.elasticsearch})</span>
    </h2>
{else}
    <h2 class="title list-group-item-heading text-inline">
        <span class="icon icon-file left" role="presentation" aria-hidden="true"></span>
        <a href="{$WWWROOT}view/view.php?id={$record->id}">{$record->title}</a>
        <span class="artefacttype text-midtone text-regular">({str tag=page section=search.elasticsearch})</span>
    </h2>
    {if $record->createdbyname}
      <div class="createdby">
        {if $record->anonymise}
            {str tag=createdbyanon section=search.elasticsearch}
        {else}
            {str tag=createdby section=search.elasticsearch arg1='<a href="`$record->createdby|profile_url`">$record->createdbyname</a>'}
        {/if}
      </div>
    {/if}
    {if $record->ownedbygroupname}
        <div class="ownedbygroup">{str tag=ownedbygroup section=search.elasticsearch arg1='<a href="`$record->ownedbygroupurl`">$record->ownedbygroupname</a>'}</div>
    {/if}
      <div class="detail">
          {if $record->highlight}
              {$record->highlight|safe}
          {else}
              {$record->description|str_shorten_html:140:true|safe}
          {/if}
      </div>
    <!-- TAGS -->
    {if is_array($record->tags) && count($record->tags) > 0}
    <div class="tags text-small">{str tag=tags section=search.elasticsearch}:
    {foreach from=$record->tags item=tag name=tags}
        <a href="{$WWWROOT}search/elasticsearch/index.php?query={$tag}&tagsonly=true">{$tag}</a>{if !$.foreach.tags.last}, {/if}
    {/foreach}
    </div>
    {/if}
    <!-- end TAGS -->
{/if}
