<tr class="{if $move->geokret->missing}danger{elseif $move->id === $move->geokret->last_position->id}success{/if}">
    <td>{$move|logicon nofilter}</td>
    <td>
        {$move->geokret|gklink nofilter} {$move->geokret|gkavatar nofilter}<br />
        <small>{$move->geokret->gkid}</small>
    </td>
    <td class="text-center">
        {$move->country|country nofilter}
        {$move|cachelink nofilter}
    </td>
    <td><span title="{$move->comment|markdown:'text'}">{$move->comment|markdown:'text'|truncate:60:"(…)" nofilter}</span></td>
    <td class="text-center" nowrap>
        {$move->moved_on_datetime|print_date nofilter}
        <br />
        <small>{$move->author|userlink:$move->username nofilter}</small>
    </td>
    <td class="text-right">
        {$move->distance|distance}
    </td>
</tr>
