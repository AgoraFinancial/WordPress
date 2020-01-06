<h2>Authentication Codes</h2>
<p>Authentication codes are the most basic level of authentication.  An authcode can correspond to any ItemCode in Advantage: Publication, Product, or an AccessMaintenanceBilling (AMB) item.
	Any authcodes defined on this page will appear at the bottom of the edit screen when editing content. It will even appear for custom post types.</p>
<p>For example, say we have a publication with a code of LD1 and we want to password protect content for it. </p>
<ol>
	<li>Give your Auth code a unique name</li>
	<li>Enter the pubcode e.g. 'LD1'</li>
	<li>Choose 'Subscriptions' from the drop-down menu</li>
	<li>Enter a description to help your editors identify the item.</li>
</ol>
<p>Now if you tag a post with 'LD1', it will be password protected for users with the LD1 publication on their account <strong>AND</strong> their circStatus is any of the following: P, Q, R, X, W</p>
<p>Note, as of version 1.1 of the plugin only Subscriptions are supported without additional rules.</p>
<p>More documentation can be found on the <a href="https://github.com/Pubsvs/Middleware-Authentication/wiki" target="_blank">Github Wiki</a></p>