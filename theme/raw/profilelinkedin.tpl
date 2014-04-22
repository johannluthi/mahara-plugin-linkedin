<table>
    <tr><td><img src="{$usr['picture-url']}"></td><td><h2>{$usr['first-name']} {$usr['last-name']}</h2></td></tr>
    <tr><td>Headline</td><td>{$usr['headline']}</td></tr>
    <tr><td>Industry</td><td>{$usr['industry']}</td></tr>
    <tr><td>Email-address</td><td>{$usr['email-address']}</td></tr>
    <tr><td>date-of-birth</td><td>{$usr['date-of-birth']['day']}/{$usr['date-of-birth']['month']}/{$usr['date-of-birth']['year']}</td></tr>
    <tr><td>associations</td><td>{$usr['associations']}</td></tr>
    <tr><td>interests</td><td>{$usr['interests'] }</td></tr>
</table>
    <h3>Skills</h3>
    {foreach from=$usr['skills'] item=ski}{$ski['skill']['name']}, {/foreach}
    <h3>Publications</h3>
    <ul>
        {foreach from=$usr['publications'] item=pub}
            <li>{$pub['title']} {$pub['publisher']}</li>
        {/foreach}
    </ul>
    <h3>Positions</h3>
    <ul>
    {foreach from=$usr['positions'] item=pos}
        <li>{$pos['title']} {$pos['summary']}</li>
    {/foreach}
    </ul>
    <h3>Educations</h3>
    <ul>
        {foreach from=$usr['educations'] item=edu}
            <li>{$edu['school-name']} {$edu['degree']}</li>
        {/foreach}
    </ul>