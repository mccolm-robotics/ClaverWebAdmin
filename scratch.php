<html lang="en">
<head>
<title>CSS Template</title>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<style>
* {
  box-sizing: border-box;
}


/* Style the header */
.settings-header {
  background-color: orange;
  padding: 2px;
  text-align: center;
  font-size: 15px;
}

/* Container for flexboxes */
.settings-container {
  display: -webkit-flex;
  display: flex;
}

/* Create two equal columns that sit next to each other */
.settings {
  padding: 10px;
}

/* Left column */
.settings.left {
   -webkit-flex: 1;
   -ms-flex: 1;
   flex: 1;
   text-align: center;
}

/* Right column */
.settings.right {
  -webkit-flex: 2;
  -ms-flex: 2;
  flex: 2;
  padding:0px;
}

/* Footer */
.settings-footer {
  background-color: #f1f1f1;
  padding: 10px;
  text-align: center;
}

/* Container for nested flexboxes */
.settings-nested-container {
  display: -webkit-flex;
  display: flex;
}

/* Create two equal columns that sit next to each other */
.settings-nested {
  padding: 10px;

}

/* Nested Left column */
.settings-nested.left {
   -webkit-flex: 1;
   -ms-flex: 1;
   flex: 1;
}

/* Nested Right column */
.settings-nested.right {
  -webkit-flex: 1;
  -ms-flex: 1;
  flex: 1;
}

/* Nested Notes*/
.settings-nested-footer {
  background-color: #f1f1f1;
  padding: 10px;
  text-align: center;
}

/* Responsive layout - makes the two settings columns and two nested columns stack on top of each other instead of next to each other */
@media (max-width: 600px) {
  .settings-container, .settings-nested-container {
    -webkit-flex-direction: column;
    flex-direction: column;
  }
}

</style>
</head>
<body>

<div class="settings-header">
    <h4>Add New User</h4>
</div>
<div class="settings-container">
    <div class="settings left" style="background-color:#aaa;">
    
        Lorem ipsum dolor sit amet, consectetur adipiscing elit. Sed eu egestas risus. Vivamus id volutpat nunc, sed varius neque. Sed efficitur gravida turpis quis malesuada. Sed quis sollicitudin est. Maecenas et enim nec lacus placerat aliquet. Nunc fringilla quis odio ac scelerisque. Aenean sed dolor sit amet nisi consequat lobortis in a dolor.

        Donec facilisis erat quis sagittis laoreet. Etiam accumsan erat nisi, ac sagittis tellus mattis quis. Praesent ut congue turpis. Pellentesque eget ipsum nibh. Praesent ultricies urna felis, ac maximus felis volutpat eu. Vestibulum sodales dui non sem mollis scelerisque. Vestibulum eu feugiat tortor, quis tincidunt metus. Maecenas a libero et dolor iaculis pharetra. Duis vestibulum lacus metus, eu convallis nisi mattis vel. Mauris sagittis fermentum sem in commodo. Etiam id purus nibh. Pellentesque habitant morbi tristique senectus et netus et malesuada fames ac turpis egestas. Quisque sed mauris pharetra, rhoncus ante ut, luctus quam. Cras vulputate magna at magna commodo imperdiet. Praesent arcu risus, aliquam ut finibus id, tincidunt laoreet lectus. Donec ac eros dolor.

        Maecenas sed lorem eget enim ullamcorper varius. Cras dui odio, luctus vel ipsum eget, fringilla convallis dolor. Sed fringilla massa id cursus pellentesque. Aliquam ac semper elit. Sed blandit feugiat rhoncus. Nullam tristique pulvinar quam vel vehicula. Nullam dignissim augue nec mauris cursus ultrices.

        Morbi volutpat eleifend tortor quis sodales. Maecenas tristique sed mauris a rhoncus. Cras nunc elit, varius sed turpis non, efficitur posuere quam. Mauris luctus cursus urna sed consequat. Maecenas eu commodo lectus. Praesent sed nibh sit amet felis ultricies egestas. Vestibulum lacinia sapien quam, et interdum diam volutpat nec. Donec porta luctus tempor. Donec hendrerit tincidunt nibh, quis gravida velit placerat et. Proin vitae aliquet ligula, eget egestas sapien. Curabitur vel egestas diam. Cras ante erat, varius vel laoreet et, fringilla eget diam. Vestibulum ante ipsum primis in faucibus orci luctus et ultrices posuere cubilia curae;

        In ullamcorper cursus fringilla. Phasellus porttitor aliquet libero eget mattis. Donec consequat lectus eu maximus laoreet. Etiam venenatis scelerisque magna, et feugiat magna placerat eu. Sed in nulla vitae massa iaculis pretium. Proin odio elit, viverra sed diam sed, dignissim interdum ex. Ut viverra lacinia dapibus. Etiam id nisi enim. Proin et dolor ut tellus interdum interdum. Aliquam tristique ex eget enim aliquet porta. 

    </div>
    <div class="settings right" style="background-color:#bbb;">
        <!-- Nested Columns -->
        <div class="settings-nested-container">
            <div class="settings-nested left" style="background-color:#aaa;">Nest-L</div>
            <div class="settings-nested right" style="background-color:#bbb;">Nest-R</div>
        </div>
        <div class="settings-nested-footer">
            <p>Notes</p>
        </div>
        <!-- End Nested Columns -->
    </div>
</div>
<div class="settings-footer">
    <!-- Form Button -->
    <p>Footer</p>
</div>

</body>
</html>



