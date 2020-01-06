<h2>Rules</h2>

<p>The rules system allows you to create more complex authentication systems. For example, you could use the rules system to restrict access based on subType, or memberCat, memberOrg fields</p>
<p>To see all the fields you can use, enter a username and password into the Debug Tools plugin</p>
<strong>Adding a Rule to an Authcode</strong>
<ol>
	<li>Clicking 'Edit' beside the authcode will show the edit menu.</li>
	<li>Click 'Add Rule'. This will display a form to create a rule for your authcode</li>
</ol>

<p><strong>Note</strong>, as soon as you add a rule to an authcode, the automatic check for circStatus is dropped, so if you want to include that you will need to add a rule for it.</p>
<p>Active circStatus values are: P, Q, R, X, W, G</p>
<p>As of Version 1.3 of the plugin the 'G' Status for Grace period has been added</p>
<p>To create a rule for circStatus choose the <em>circStatus</em> field, set the operator to <em>Contained In</em> and the value to <em>P, Q, R, X, W, G</em></p>

<p><strong>Shortcodes available: </strong> {{now}} returns current time on the server (ensure time is correct)</p>

<p>Rules follow <strong>AND</strong> logic. If you have multiple rules on an authcode they must <strong>all</strong> be true for the user to be allowed view the content.</p>