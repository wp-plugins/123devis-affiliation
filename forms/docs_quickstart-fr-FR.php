<div class="wrap">
<h2>Documentation</h2>
<h3>Introduction</h3>

<p>Le plugin WordPress 123devis est là pour vous aider à facilement intégrer les formulaires 123devis sur votre site et ainsi générer du revenu. Pour bien débuter, suivez les étapes ci-dessous en commencant par cliquer sur le bouton 123devis dans le menu de gauche (1).</p>
<img style="float:right" src="<?php print plugins_url('settings.png', __FILE__);?>">

<h3>Configurer l’API (2)</h3>
<p>
	La section inférieure de cette page vous permet de configurer l’url de l’API en utilisant les informations fournies par votre contact 123devis. 
</p>
<p>L’url devrait être <b>"https://api.servicemagic.eu"</b>.</p>
<p>Pour le serveur, sélectionnez <b>“FR”</b> pour France.</p>

<h3>Vos identifiants (3)</h3>
<p>Entrez les identifiants fournis par votre contact 123devis et cliquez sur <b>“Sauvegarder”</b>. Si vous rencontrez un problème, vérifiez que l’url de l’API est correcte ou bien contactez 123devis.</p>

<?php if (get_option('sm_creds')) { ?>

<br style="clear:both"/>
<hr/>
<h3>Créer un formulaire intégrable </h3>

<img style="float:right" src="<?php print plugins_url('to_embeddable_form.png', __FILE__);?>">
<ol>
<li>Maintenant que vos identifiants sont sauvegardés, le plugin 123devis affiche un menu plus étendu (1).</li>
<li>Cliquez sur “Formulaires intégrables” (2),</li>
<li>Puis "Créer nouveau formulaire"(3).</li>
</ol>
<p>Choisissez le type d’affichage que vous désirez (1 ou 2 étapes) puis commencez la configuration du formulaire.</p>
<p>Le <b>“Nom du formulaire”</b> sert à retrouver facilement ce formulaire dans l’interface WordPress. Il est utilisé dans la liste des formulaires intégrables, dans les short-codes au niveau des articles, pages ou de l’éditeur de texte.</p>
<p>Le Code de tracking vous permet de séparer ce formulaire des autres dans les rapports de résultat. </p>
<p>Choisissez ensuite le formulaire 123devis que vous souhaitez utiliser. Selectionnez la catégorie désirée pour faire apparaitre la liste des formulaires disponibles. Une fois un formulaire sélectionné, vous pouvez toujours revenir en arrière en cliquant sur <b>“Modifier”</b>. </p>
<p>Les options d’affichage vous permettent ensuite de personnaliser votre formulaire. Affichez ou non les champs optionnels. Ajouter ou non du texte en haut de page du formulaire.</p>
<hr>

<img style="float:right"  src="<?php print plugins_url('shortcodes.png', __FILE__);?>"><h3>Intégrer un formulaire à un article ou une page en utilisant le short-code</h3>
<ol>
<li>Choisir où vous souhaitez faire afficher le formulaire En cliquant sur <b>Articles</b> ou <b>Pages</b> dans le menu principal.</li>
<li>Une fois dans l’éditeur de texte, cliquez sur le générateur de shortcode 123devis (2).</li>
<li>Sélectionnez le formulaire voulu dans la liste des formulaires intégrables (3).</li>
<li>Le shortcode est automatiquement inséré au contenu de l’article/la page.</li>
<li>Vous pouvez maintenant voir votre formulaire 123devis en cliquant sur <b>Aperçu</b>. (5) </li>
</ol>
<?php } ?>
</div>